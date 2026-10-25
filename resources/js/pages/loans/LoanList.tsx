import { isAxiosError } from "axios";
import { Banknote, Search } from "lucide-react";
import { useCallback, useEffect, useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import BackButton from "@/components/layout/BackButton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Empty, EmptyDescription, EmptyMedia, EmptyTitle } from "@/components/ui/empty";
import { Input } from "@/components/ui/input";
import { Progress } from "@/components/ui/progress";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Skeleton } from "@/components/ui/skeleton";
import { api } from "@/lib/api";
import { formatIDR } from "@/lib/format";
import type { Loan } from "@/lib/types";
import { usePageTitle } from "@/lib/usePageTitle";

export const loanProgressClass =
    "w-full h-2 gap-0 overflow-hidden rounded-full bg-gray-200 [&_[data-slot=progress-track]]:h-2 [&_[data-slot=progress-track]]:rounded-full [&_[data-slot=progress-track]]:bg-gray-200 [&_[data-slot=progress-indicator]]:bg-green-500";

export function LoanProgress({ percent }: { percent: number }) {
    return <Progress value={percent} className={loanProgressClass} />;
}

function formatFullDate(dateString: string) {
    return new Date(dateString).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

type StatusFilter = "" | "paid" | "unpaid";

export default function LoanList() {
    usePageTitle("Daftar Pinjaman");
    const navigate = useNavigate();

    const [loans, setLoans] = useState<Loan[]>([]);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState("");
    const [filterName, setFilterName] = useState("");
    const [sortDate, setSortDate] = useState<"desc" | "asc">("desc");
    const [filterStatus, setFilterStatus] = useState<StatusFilter>("");

    const [deleteTarget, setDeleteTarget] = useState<Loan | null>(null);
    const [deleting, setDeleting] = useState(false);

    const loadLoans = useCallback(async () => {
        setLoadError("");
        setLoading(true);
        try {
            const url = filterStatus ? `/loans?status=${filterStatus}` : "/loans";
            const res = await api.get(url);
            setLoans((res.data.data ?? []) as Loan[]);
        } catch (err) {
            console.error(err);
            setLoadError("Gagal memuat data pinjaman.");
        } finally {
            setLoading(false);
        }
    }, [filterStatus]);

    useEffect(() => {
        loadLoans();
    }, [loadLoans]);

    const filtered = loans
        .filter((l) => {
            const nameMatch = l.borrower.name
                .toLowerCase()
                .includes(filterName.toLowerCase());
            const statusMatch =
                !filterStatus ||
                (filterStatus === "paid" && l.remaining === 0) ||
                (filterStatus === "unpaid" && l.remaining > 0);
            return nameMatch && statusMatch;
        })
        .sort((a, b) =>
            sortDate === "asc"
                ? new Date(a.date).getTime() - new Date(b.date).getTime()
                : new Date(b.date).getTime() - new Date(a.date).getTime(),
        );

    const paidLoans = filtered.filter((l) => l.remaining === 0);
    const unpaidLoans = filtered.filter((l) => l.remaining > 0);
    const paidTotal = paidLoans.reduce((sum, l) => sum + l.paid, 0);
    const unpaidTotal = unpaidLoans.reduce((sum, l) => sum + l.remaining, 0);

    async function handleDelete(e: FormEvent) {
        e.preventDefault();
        if (!deleteTarget) return;

        setDeleting(true);
        try {
            await api.delete(`/loans/${deleteTarget.id}`);
            setDeleteTarget(null);
            await loadLoans();
        } catch (err) {
            const message = isAxiosError(err)
                ? ((err.response?.data as { message?: string } | undefined)?.message ??
                  "Gagal menghapus pinjaman.")
                : "Gagal menghapus pinjaman.";
            alert(message);
        } finally {
            setDeleting(false);
        }
    }

    return (
        <div>
            <div className="mb-4 flex items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                    <BackButton />
                    <h1 className="text-2xl font-bold">Daftar Pinjaman</h1>
                </div>
                <Badge variant="secondary">{filtered.length} pinjaman</Badge>
            </div>

            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="search"
                        placeholder="Cari peminjam..."
                        className="bg-white pl-8"
                        value={filterName}
                        onChange={(e) => setFilterName(e.target.value)}
                    />
                </div>
                <div className="grid grid-cols-2 gap-2 sm:flex">
                    <Select
                        value={sortDate}
                        onValueChange={(v) => setSortDate(v as "desc" | "asc")}
                    >
                        <SelectTrigger className="w-full bg-white text-sm sm:w-32">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="desc">Terbaru</SelectItem>
                            <SelectItem value="asc">Terlama</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        value={filterStatus || "all"}
                        onValueChange={(v) => setFilterStatus(v === "all" ? "" : (v as StatusFilter))}
                    >
                        <SelectTrigger className="w-full bg-white text-sm sm:w-40">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua</SelectItem>
                            <SelectItem value="paid">Lunas</SelectItem>
                            <SelectItem value="unpaid">Belum Lunas</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="mb-4 grid grid-cols-2 gap-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-xs font-medium text-muted-foreground">Total Lunas</p>
                        {loading ? (
                            <Skeleton className="mt-2 h-7 w-24" />
                        ) : (
                            <>
                                <p className="text-2xl font-semibold tabular-nums">
                                    {paidLoans.length}{" "}
                                    <span className="text-sm font-normal text-muted-foreground">pinjaman</span>
                                </p>
                                <p className="text-xs font-medium text-emerald-600">
                                    {formatIDR(paidTotal)}
                                </p>
                            </>
                        )}
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-xs font-medium text-muted-foreground">Total Belum Lunas</p>
                        {loading ? (
                            <Skeleton className="mt-2 h-7 w-24" />
                        ) : (
                            <>
                                <p className="text-2xl font-semibold tabular-nums">
                                    {unpaidLoans.length}{" "}
                                    <span className="text-sm font-normal text-muted-foreground">pinjaman</span>
                                </p>
                                <p className="text-xs font-medium text-red-600">
                                    {formatIDR(unpaidTotal)}
                                </p>
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>

            {loadError && <p className="mb-3 text-sm text-destructive">{loadError}</p>}

            <div className="space-y-3">
                {loading && (
                    <>
                        <Skeleton className="h-44 w-full" />
                        <Skeleton className="h-44 w-full" />
                        <Skeleton className="h-44 w-full" />
                    </>
                )}

                {!loading && filtered.length === 0 && !loadError && (
                    <Empty>
                        <EmptyMedia variant="icon">
                            <Banknote />
                        </EmptyMedia>
                        <EmptyTitle>Tidak ada pinjaman</EmptyTitle>
                        <EmptyDescription>
                            Ubah filter pencarianmu atau tambah pinjaman baru.
                        </EmptyDescription>
                    </Empty>
                )}

                {!loading &&
                    filtered.map((loan) => {
                        const isPaid = loan.status === "paid" || loan.remaining === 0;

                        return (
                            <Card key={loan.id}>
                                <CardContent className="space-y-3 p-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <h2 className="truncate font-semibold">
                                                    {loan.borrower.name}
                                                </h2>
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
                                                {formatFullDate(loan.date)}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-xl font-semibold tabular-nums">
                                                {formatIDR(loan.total_amount)}
                                            </p>
                                            <p className="text-xs text-muted-foreground">total</p>
                                        </div>
                                    </div>

                                    <div className="space-y-1.5 border-t pt-2.5 text-sm">
                                        <div className="flex justify-between gap-2">
                                            <span className="text-muted-foreground">Sudah dibayar</span>
                                            <span className="font-medium tabular-nums text-emerald-600">
                                                {formatIDR(loan.paid)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between gap-2">
                                            <span className="text-muted-foreground">Sisa</span>
                                            <span className="font-medium tabular-nums text-red-600">
                                                {formatIDR(loan.remaining)}
                                            </span>
                                        </div>
                                    </div>

                                    <LoanProgress percent={loan.percent} />

                                    <div className="flex justify-end gap-2">
                                        {!isPaid && (
                                            <Button
                                                variant="outline"
                                                size="xs"
                                                onClick={() => navigate(`/loans/${loan.id}/pay`)}
                                            >
                                                Bayar
                                            </Button>
                                        )}
                                        <Button
                                            variant="outline"
                                            size="xs"
                                            onClick={() => navigate(`/loans/${loan.id}`)}
                                        >
                                            Detail
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            size="xs"
                                            onClick={() => setDeleteTarget(loan)}
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
            </div>

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(open) => {
                    if (!open) setDeleteTarget(null);
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Hapus Pinjaman</DialogTitle>
                    </DialogHeader>

                    <p className="mb-4 text-sm">
                        Anda yakin ingin menghapus pinjaman ini? Data akan{" "}
                        <span className="font-bold text-red-600">hilang permanen</span>!
                    </p>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setDeleteTarget(null)}
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            disabled={deleting}
                            onClick={handleDelete}
                        >
                            {deleting ? "Menghapus..." : "Hapus"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
