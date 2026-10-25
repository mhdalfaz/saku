import { isAxiosError } from "axios";
import { Archive, FileText, ImageIcon } from "lucide-react";
import { useCallback, useEffect, useState, type FormEvent } from "react";
import { useParams } from "react-router-dom";
import Breadcrumbs from "@/components/layout/Breadcrumbs";
import BackButton from "@/components/layout/BackButton";
import {
    Attachment,
    AttachmentAction,
    AttachmentActions,
    AttachmentContent,
    AttachmentDescription,
    AttachmentMedia,
    AttachmentTitle,
    AttachmentTrigger,
} from "@/components/ui/attachment";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Empty, EmptyDescription, EmptyMedia, EmptyTitle } from "@/components/ui/empty";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { api } from "@/lib/api";
import { formatIDR, unformatIDR } from "@/lib/format";
import type {
    Attachment as AttachmentRecord,
    Saving,
    SavingTransaction,
    SavingTransactionType,
} from "@/lib/types";
import { usePageTitle } from "@/lib/usePageTitle";

function formatFullDate(dateString: string) {
    return new Date(dateString).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

function fileExt(name: string) {
    const index = name.lastIndexOf(".");
    return index > -1 ? name.slice(index + 1).toUpperCase() : "FILE";
}

function errorMessage(err: unknown, fallback: string) {
    if (isAxiosError(err)) {
        return (
            (err.response?.data as { message?: string } | undefined)?.message ??
            fallback
        );
    }
    return fallback;
}

type TransactionModalType = Extract<
    SavingTransactionType,
    "deposit" | "withdrawal" | "profit"
>;

const transactionTitles: Record<TransactionModalType, string> = {
    deposit: "Tambah Setoran",
    withdrawal: "Tarik Simpanan",
    profit: "Catat Keuntungan",
};

const transactionPlaceholders: Record<TransactionModalType, string> = {
    deposit: "500000",
    withdrawal: "500000",
    profit: "50000",
};

const transactionSubmitLabels: Record<TransactionModalType, string> = {
    deposit: "Simpan Setoran",
    withdrawal: "Simpan Penarikan",
    profit: "Simpan Keuntungan",
};

interface TransactionDialogProps {
    type: TransactionModalType;
    savingId: number;
    balance: number;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSuccess: () => Promise<void>;
}

function TransactionDialog({
    type,
    savingId,
    balance,
    open,
    onOpenChange,
    onSuccess,
}: TransactionDialogProps) {
    const [amount, setAmount] = useState("");
    const [note, setNote] = useState("");
    const [retained, setRetained] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        if (open) {
            setAmount("");
            setNote("");
            setRetained(false);
            setError("");
        }
    }, [open]);

    async function handleSubmit(e: FormEvent) {
        e.preventDefault();

        const value = unformatIDR(amount);
        if (!value || value < 1) {
            setError("Nominal harus lebih dari 0.");
            return;
        }

        setSubmitting(true);
        setError("");

        try {
            const payload: Record<string, unknown> = {
                amount: value,
                note: note.trim() || null,
            };
            if (type === "profit") payload.retained = retained;

            const res = await api.post(`/savings/${savingId}/${type}`, payload);
            alert(res.data.message as string);
            onOpenChange(false);
            await onSuccess();
        } catch (err) {
            setError(errorMessage(err, "Gagal menyimpan transaksi."));
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{transactionTitles[type]}</DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-3">
                    {type === "withdrawal" && (
                        <p className="text-sm text-muted-foreground">
                            Saldo saat ini:{" "}
                            <span className="font-medium text-foreground">
                                {formatIDR(balance)}
                            </span>
                        </p>
                    )}

                    {error && <p className="text-sm text-destructive">{error}</p>}

                    <div>
                        <label className="mb-1 block text-sm font-medium" htmlFor={`${type}-amount`}>
                            Nominal{" "}
                            {type === "deposit"
                                ? "Setoran"
                                : type === "withdrawal"
                                  ? "Penarikan"
                                  : "Keuntungan"}{" "}
                            (IDR)
                        </label>
                        <Input
                            id={`${type}-amount`}
                            type="text"
                            placeholder={transactionPlaceholders[type]}
                            value={amount}
                            onChange={(e) => setAmount(formatIDR(e.target.value))}
                        />
                    </div>

                    <div>
                        <label className="mb-1 block text-sm font-medium" htmlFor={`${type}-note`}>
                            Catatan (opsional)
                        </label>
                        <Input
                            id={`${type}-note`}
                            type="text"
                            placeholder={
                                type === "deposit"
                                    ? "Misal: setoran bulan ini"
                                    : type === "withdrawal"
                                      ? "Misal: ditarik via transfer"
                                      : "Misal: bagi hasil kuartal ini"
                            }
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                        />
                    </div>

                    {type === "profit" && (
                        <label className="flex items-start gap-2 text-sm">
                            <input
                                type="checkbox"
                                className="mt-1"
                                checked={retained}
                                onChange={(e) => setRetained(e.target.checked)}
                            />
                            <span>
                                Profit ditahan/direinvestasi di penampung (menambah saldo
                                tertahan)
                            </span>
                        </label>
                    )}

                    <Button type="submit" className="w-full" disabled={submitting}>
                        {submitting ? "Menyimpan..." : transactionSubmitLabels[type]}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function SavingDetail() {
    usePageTitle("Detail Simpanan");
    const { savingId } = useParams<{ savingId: string }>();

    const [saving, setSaving] = useState<Saving | null>(null);
    const [loadError, setLoadError] = useState("");

    const [transactionModal, setTransactionModal] = useState<TransactionModalType | null>(
        null,
    );
    const [closeOpen, setCloseOpen] = useState(false);
    const [closing, setClosing] = useState(false);
    const [deleteTransactionTarget, setDeleteTransactionTarget] =
        useState<SavingTransaction | null>(null);
    const [deletingTransaction, setDeletingTransaction] = useState(false);
    const [previewImage, setPreviewImage] = useState<AttachmentRecord | null>(null);

    const loadSaving = useCallback(async () => {
        setLoadError("");
        try {
            const res = await api.get(`/savings/${savingId}`);
            setSaving(res.data.data as Saving);
        } catch (err) {
            console.error(err);
            setLoadError("Gagal memuat data simpanan.");
        }
    }, [savingId]);

    useEffect(() => {
        loadSaving();
    }, [loadSaving]);

    async function handleCloseSaving() {
        if (!saving) return;
        setClosing(true);
        try {
            const res = await api.post(`/savings/${saving.id}/close`);
            setCloseOpen(false);
            alert(res.data.message as string);
            await loadSaving();
        } catch (err) {
            alert(errorMessage(err, "Gagal menutup simpanan."));
        } finally {
            setClosing(false);
        }
    }

    async function handleDeleteTransaction(e: FormEvent) {
        e.preventDefault();
        if (!deleteTransactionTarget) return;

        setDeletingTransaction(true);
        try {
            await api.delete(`/saving-transactions/${deleteTransactionTarget.id}`);
            setDeleteTransactionTarget(null);
            await loadSaving();
        } catch (err) {
            alert(errorMessage(err, "Gagal menghapus transaksi."));
        } finally {
            setDeletingTransaction(false);
        }
    }

    if (loadError) {
        return <p className="text-sm text-destructive">{loadError}</p>;
    }

    if (!saving) {
        return (
            <div className="space-y-3">
                <Skeleton className="h-56 w-full" />
                <Skeleton className="h-40 w-full" />
            </div>
        );
    }

    const images = saving.images ?? [];
    const files = saving.files ?? [];
    const hasAttachments = images.length > 0 || files.length > 0;

    return (
        <div>
            <div className="mb-1 flex items-center gap-2">
                <BackButton />
                <h1 className="text-2xl font-bold">Detail Simpanan</h1>
            </div>
            <Breadcrumbs />

            <Card>
                <CardContent className="space-y-3">
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            <div className="flex items-center gap-2">
                                <h2 className="truncate font-semibold">{saving.custodian.name}</h2>
                                {saving.status === "ongoing" ? (
                                    <Badge
                                        variant="outline"
                                        className="border-emerald-500/20 bg-emerald-500/10 text-emerald-600"
                                    >
                                        Aktif
                                    </Badge>
                                ) : (
                                    <Badge variant="secondary">Ditutup</Badge>
                                )}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Dibuka: {formatFullDate(saving.date)}
                            </p>
                        </div>
                        <div className="text-right">
                            <p className="text-2xl font-semibold tabular-nums">
                                {formatIDR(saving.balance)}
                            </p>
                            <p className="text-xs text-muted-foreground">saldo tertahan</p>
                        </div>
                    </div>

                    {saving.description && (
                        <p className="border-t pt-2.5 text-sm text-muted-foreground">
                            {saving.description}
                        </p>
                    )}

                    <dl className="space-y-1.5 border-t pt-2.5 text-sm">
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Total setoran</dt>
                            <dd className="font-medium tabular-nums text-emerald-600">
                                {formatIDR(saving.total_deposit)}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Total penarikan</dt>
                            <dd className="font-medium tabular-nums text-red-600">
                                {formatIDR(saving.total_withdrawal)}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Total keuntungan</dt>
                            <dd className="font-medium tabular-nums text-blue-600">
                                {formatIDR(saving.total_profit)}
                                <span className="ml-1 font-normal text-muted-foreground">
                                    ({formatIDR(saving.profit_retained)} ditahan)
                                </span>
                            </dd>
                        </div>
                    </dl>

                    {saving.status === "ongoing" && (
                        <div className="flex flex-wrap gap-2 border-t pt-3">
                            <Button onClick={() => setTransactionModal("deposit")}>
                                Setor
                            </Button>
                            <Button
                                variant="destructive"
                                onClick={() => setTransactionModal("withdrawal")}
                            >
                                Tarik
                            </Button>
                            <Button
                                variant="outline"
                                onClick={() => setTransactionModal("profit")}
                            >
                                Keuntungan
                            </Button>
                            <Button variant="secondary" onClick={() => setCloseOpen(true)}>
                                Tutup
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>

            {hasAttachments && (
                <div className="mt-6">
                    <h2 className="mb-2 text-lg font-semibold">Lampiran</h2>
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {images.map((img) => (
                            <Attachment
                                key={`img-${img.id}`}
                                state="done"
                                className="w-full max-w-full"
                            >
                                <AttachmentMedia variant="image">
                                    <img src={img.public_url} alt={img.original_name} />
                                </AttachmentMedia>
                                <AttachmentContent>
                                    <AttachmentTitle>{img.original_name}</AttachmentTitle>
                                    <AttachmentDescription>
                                        {fileExt(img.original_name)} · klik untuk preview
                                    </AttachmentDescription>
                                </AttachmentContent>
                                <AttachmentActions>
                                    <AttachmentAction
                                        aria-label={`Preview ${img.original_name}`}
                                        onClick={() => setPreviewImage(img)}
                                    >
                                        <ImageIcon />
                                    </AttachmentAction>
                                </AttachmentActions>
                                <AttachmentTrigger
                                    aria-label={`Preview ${img.original_name}`}
                                    onClick={() => setPreviewImage(img)}
                                />
                            </Attachment>
                        ))}

                        {files.map((file) => {
                            const isPdf =
                                file.mime_type === "application/pdf" ||
                                file.original_name.toLowerCase().endsWith(".pdf");

                            return (
                                <Attachment
                                    key={`file-${file.id}`}
                                    state="done"
                                    className="w-full max-w-full"
                                >
                                    <AttachmentMedia>
                                        <FileText />
                                    </AttachmentMedia>
                                    <AttachmentContent>
                                        <AttachmentTitle>{file.original_name}</AttachmentTitle>
                                        <AttachmentDescription>
                                            {isPdf ? "PDF" : fileExt(file.original_name)} · buka di
                                            tab baru
                                        </AttachmentDescription>
                                    </AttachmentContent>
                                    <AttachmentActions>
                                        <AttachmentAction
                                            aria-label={`Buka ${file.original_name}`}
                                            render={
                                                <a
                                                    href={file.public_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                />
                                            }
                                        >
                                            Buka
                                        </AttachmentAction>
                                    </AttachmentActions>
                                    <AttachmentTrigger
                                        render={
                                            <a
                                                href={file.public_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                aria-label={`Buka ${file.original_name}`}
                                            />
                                        }
                                    />
                                </Attachment>
                            );
                        })}
                    </div>
                </div>
            )}

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle className="text-lg">Riwayat Transaksi</CardTitle>
                </CardHeader>
                <CardContent>
                    {saving.transactions.length === 0 ? (
                        <Empty className="border-0 p-4">
                            <EmptyMedia variant="icon">
                                <Archive />
                            </EmptyMedia>
                            <EmptyTitle>Belum ada transaksi</EmptyTitle>
                            <EmptyDescription>
                                Transaksi setoran, penarikan, dan keuntungan akan muncul di
                                sini.
                            </EmptyDescription>
                        </Empty>
                    ) : (
                        <div className="space-y-3">
                            {saving.transactions.map((t) => {
                                const typeConfig: Record<
                                    string,
                                    { label: string; cls: string }
                                > = {
                                    deposit: {
                                        label: "Setoran",
                                        cls: "border-emerald-500/20 bg-emerald-500/10 text-emerald-600",
                                    },
                                    withdrawal: {
                                        label: "Penarikan",
                                        cls: "border-red-500/20 bg-red-500/10 text-red-600",
                                    },
                                    profit: {
                                        label: "Keuntungan",
                                        cls: "border-blue-500/20 bg-blue-500/10 text-blue-600",
                                    },
                                };
                                const config = typeConfig[t.type] ?? {
                                    label: t.type,
                                    cls: "",
                                };
                                const retainedLabel =
                                    t.type === "profit"
                                        ? t.retained
                                            ? " (ditahan)"
                                            : " (diterima)"
                                        : "";

                                return (
                                    <div
                                        key={t.id}
                                        className="flex items-start justify-between gap-3 border-t pt-3 first:border-t-0 first:pt-0"
                                    >
                                        <div className="min-w-0">
                                            <Badge variant="outline" className={config.cls}>
                                                {config.label}
                                                {retainedLabel}
                                            </Badge>
                                            <p className="mt-1 text-lg font-semibold tabular-nums">
                                                {formatIDR(t.amount)}
                                            </p>
                                            {t.note && (
                                                <p className="text-sm text-muted-foreground">
                                                    Catatan: {t.note}
                                                </p>
                                            )}
                                        </div>
                                        <div className="flex flex-col items-end gap-2">
                                            <p className="text-xs text-muted-foreground">
                                                {formatFullDate(t.date)}
                                            </p>
                                            <Button
                                                variant="destructive"
                                                size="xs"
                                                onClick={() => setDeleteTransactionTarget(t)}
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </CardContent>
            </Card>

            {transactionModal && (
                <TransactionDialog
                    type={transactionModal}
                    savingId={saving.id}
                    balance={saving.balance}
                    open={transactionModal !== null}
                    onOpenChange={(open) => {
                        if (!open) setTransactionModal(null);
                    }}
                    onSuccess={loadSaving}
                />
            )}

            <Dialog open={closeOpen} onOpenChange={setCloseOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tutup Simpanan</DialogTitle>
                    </DialogHeader>

                    <p className="mb-4 text-sm">
                        {saving.balance > 0 ? (
                            <>
                                Saldo tertahan masih{" "}
                                <span className="font-bold">{formatIDR(saving.balance)}</span>.
                                Pastikan dana sudah dikembalikan oleh penampung sebelum
                                menutup simpanan.
                            </>
                        ) : (
                            <>
                                Anda yakin ingin menutup simpanan ini? Simpanan yang ditutup
                                tidak bisa menerima transaksi baru.
                            </>
                        )}
                    </p>

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setCloseOpen(false)}>
                            Batal
                        </Button>
                        <Button
                            variant="destructive"
                            disabled={closing}
                            onClick={handleCloseSaving}
                        >
                            {closing ? "Menutup..." : "Tutup Simpanan"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog
                open={deleteTransactionTarget !== null}
                onOpenChange={(open) => {
                    if (!open) setDeleteTransactionTarget(null);
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus Transaksi</DialogTitle>
                    </DialogHeader>

                    <p className="mb-4 text-sm">
                        Yakin ingin menghapus transaksi ini? Data akan{" "}
                        <span className="font-bold text-destructive">hilang permanen</span>!
                    </p>

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteTransactionTarget(null)}
                        >
                            Batal
                        </Button>
                        <Button
                            variant="destructive"
                            disabled={deletingTransaction}
                            onClick={handleDeleteTransaction}
                        >
                            {deletingTransaction ? "Menghapus..." : "Hapus"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog
                open={previewImage !== null}
                onOpenChange={(open) => {
                    if (!open) setPreviewImage(null);
                }}
            >
                <DialogContent className="max-w-3xl">
                    {previewImage && (
                        <div className="flex flex-col items-center justify-center p-6">
                            <img
                                src={previewImage.public_url}
                                alt={previewImage.original_name}
                                className="max-h-[500px] max-w-full rounded-none object-contain"
                            />
                            <p className="mt-2 text-xs text-muted-foreground">
                                {previewImage.original_name}
                            </p>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
