import { isAxiosError } from "axios";
import { Banknote, FileText, ImageIcon } from "lucide-react";
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
import { Skeleton } from "@/components/ui/skeleton";
import { LoanProgress } from "@/pages/loans/LoanList";
import { api } from "@/lib/api";
import { formatIDR } from "@/lib/format";
import type { Attachment as AttachmentRecord, Loan, LoanTransaction } from "@/lib/types";
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

export default function LoanDetail() {
    usePageTitle("Detail Pinjaman");
    const { loanId } = useParams<{ loanId: string }>();

    const [loan, setLoan] = useState<Loan | null>(null);
    const [loadError, setLoadError] = useState("");

    const [deleteTransactionTarget, setDeleteTransactionTarget] =
        useState<LoanTransaction | null>(null);
    const [deletingTransaction, setDeletingTransaction] = useState(false);
    const [previewImage, setPreviewImage] = useState<AttachmentRecord | null>(null);

    const loadLoan = useCallback(async () => {
        setLoadError("");
        try {
            const res = await api.get(`/loans/${loanId}`);
            setLoan(res.data.data as Loan);
        } catch (err) {
            console.error(err);
            setLoadError("Gagal memuat data pinjaman.");
        }
    }, [loanId]);

    useEffect(() => {
        loadLoan();
    }, [loadLoan]);

    async function handleDeleteTransaction(e: FormEvent) {
        e.preventDefault();
        if (!deleteTransactionTarget) return;

        setDeletingTransaction(true);
        try {
            await api.delete(`/loan-transactions/${deleteTransactionTarget.id}`);
            setDeleteTransactionTarget(null);
            await loadLoan();
        } catch (err) {
            alert(errorMessage(err, "Gagal menghapus transaksi."));
        } finally {
            setDeletingTransaction(false);
        }
    }

    if (loadError) {
        return <p className="text-sm text-destructive">{loadError}</p>;
    }

    if (!loan) {
        return (
            <div className="space-y-3">
                <Skeleton className="h-56 w-full" />
                <Skeleton className="h-40 w-full" />
            </div>
        );
    }

    const images = loan.images ?? [];
    const files = loan.files ?? [];
    const hasAttachments = images.length > 0 || files.length > 0;
    const paid = loan.paid_amount ?? loan.paid;
    const remaining = loan.remaining_amount ?? loan.remaining;
    const isPaid = loan.status === "paid" || remaining === 0;

    return (
        <div>
            <div className="mb-1 flex items-center gap-2">
                <BackButton />
                <h1 className="text-2xl font-bold">Detail Pinjaman</h1>
            </div>
            <Breadcrumbs />

            <Card>
                <CardContent className="space-y-3">
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            <div className="flex items-center gap-2">
                                <h2 className="truncate font-semibold">{loan.borrower.name}</h2>
                                {isPaid ? (
                                    <Badge
                                        variant="outline"
                                        className="border-emerald-500/20 bg-emerald-500/10 text-emerald-600"
                                    >
                                        Lunas
                                    </Badge>
                                ) : (
                                    <Badge variant="secondary">Belum Lunas</Badge>
                                )}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Tanggal Pinjaman: {formatFullDate(loan.date)}
                            </p>
                        </div>
                        <div className="text-right">
                            <p className="text-2xl font-semibold tabular-nums">
                                {formatIDR(loan.total_amount)}
                            </p>
                            <p className="text-xs text-muted-foreground">total</p>
                        </div>
                    </div>

                    <dl className="space-y-1.5 border-t pt-2.5 text-sm">
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Sudah dibayar</dt>
                            <dd className="font-medium tabular-nums text-emerald-600">
                                {formatIDR(paid)}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Sisa</dt>
                            <dd className="font-medium tabular-nums text-red-600">
                                {formatIDR(remaining)}
                            </dd>
                        </div>
                    </dl>

                    <LoanProgress percent={loan.percent} />
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
                    <CardTitle className="text-lg">Riwayat Pembayaran</CardTitle>
                </CardHeader>
                <CardContent>
                    {loan.transactions.length === 0 ? (
                        <Empty className="border-0 p-4">
                            <EmptyMedia variant="icon">
                                <Banknote />
                            </EmptyMedia>
                            <EmptyTitle>Belum ada transaksi pembayaran</EmptyTitle>
                            <EmptyDescription>
                                Pembayaran yang kamu catat akan muncul di sini.
                            </EmptyDescription>
                        </Empty>
                    ) : (
                        <div className="space-y-3">
                            {loan.transactions.map((t) => (
                                <div
                                    key={t.id}
                                    className="flex items-start justify-between gap-3 border-t pt-3 first:border-t-0 first:pt-0"
                                >
                                    <div className="min-w-0">
                                        <p className="text-lg font-semibold tabular-nums">
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
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            <Dialog
                open={deleteTransactionTarget !== null}
                onOpenChange={(open) => {
                    if (!open) setDeleteTransactionTarget(null);
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus Pembayaran</DialogTitle>
                    </DialogHeader>

                    <p className="mb-4 text-sm">
                        Yakin ingin menghapus pembayaran ini? Data akan{" "}
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
