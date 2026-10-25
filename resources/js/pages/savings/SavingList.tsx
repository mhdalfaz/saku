import { isAxiosError } from "axios";
import { Archive, Search } from "lucide-react";
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
import type { Saving, SavingStatus } from "@/lib/types";
import { usePageTitle } from "@/lib/usePageTitle";

function formatFullDate(dateString: string) {
    return new Date(dateString).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

export default function SavingList() {
    usePageTitle("Daftar Simpanan");
    const navigate = useNavigate();

    const [savings, setSavings] = useState<Saving[]>([]);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState("");
    const [filterName, setFilterName] = useState("");
    const [sortDate, setSortDate] = useState<"desc" | "asc">("desc");
    const [filterStatus, setFilterStatus] = useState<"" | SavingStatus>("");

    const [deleteTarget, setDeleteTarget] = useState<Saving | null>(null);
    const [deleting, setDeleting] = useState(false);

    const loadSavings = useCallback(async () => {
        setLoadError("");
        setLoading(true);
        try {
            const url = filterStatus ? `/savings?status=${filterStatus}` : "/savings";
            const res = await api.get(url);
            setSavings((res.data.data ?? []) as Saving[]);
        } catch (err) {
            console.error(err);
            setLoadError("Gagal memuat data simpanan.");
        } finally {
            setLoading(false);
        }
    }, [filterStatus]);

    useEffect(() => {
        loadSavings();
    }, [loadSavings]);

    const filtered = savings
        .filter((s) => {
            const nameMatch = s.custodian.name
                .toLowerCase()
                .includes(filterName.toLowerCase());
            const statusMatch = !filterStatus || s.status === filterStatus;
            return nameMatch && statusMatch;
        })
        .sort((a, b) =>
            sortDate === "asc"
                ? new Date(a.date).getTime() - new Date(b.date).getTime()
                : new Date(b.date).getTime() - new Date(a.date).getTime(),
        );

    const totalBalance = filtered.reduce((sum, s) => sum + s.balance, 0);
    const totalProfit = filtered.reduce((sum, s) => sum + s.total_profit, 0);
    const ongoingCount = filtered.filter((s) => s.status === "ongoing").length;

    async function handleDelete(e: FormEvent) {
        e.preventDefault();
        if (!deleteTarget) return;

        setDeleting(true);
        try {
            await api.delete(`/savings/${deleteTarget.id}`);
            setDeleteTarget(null);
            await loadSavings();
        } catch (err) {
            const message = isAxiosError(err)
                ? ((err.response?.data as { message?: string } | undefined)?.message ??
                  "Gagal menghapus simpanan.")
                : "Gagal menghapus simpanan.";
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
                    <h1 className="text-2xl font-bold">Daftar Simpanan</h1>
                </div>
                <Badge variant="secondary">{filtered.length} simpanan</Badge>
            </div>

            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="search"
                        placeholder="Cari penampung..."
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
                        onValueChange={(v) => setFilterStatus(v === "all" ? "" : (v as SavingStatus))}
                    >
                        <SelectTrigger className="w-full bg-white text-sm sm:w-36">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua</SelectItem>
                            <SelectItem value="ongoing">Aktif</SelectItem>
                            <SelectItem value="closed">Ditutup</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="mb-4 grid grid-cols-2 gap-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-xs font-medium text-muted-foreground">Saldo Tertahan</p>
                        {loading ? (
                            <Skeleton className="mt-2 h-7 w-24" />
                        ) : (
                            <p className="text-2xl font-semibold tabular-nums text-blue-600">
                                {formatIDR(totalBalance)}
                            </p>
                        )}
                        <p className="text-xs text-muted-foreground">{ongoingCount} simpanan aktif</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-xs font-medium text-muted-foreground">Total Keuntungan</p>
                        {loading ? (
                            <Skeleton className="mt-2 h-7 w-24" />
                        ) : (
                            <p className="text-2xl font-semibold tabular-nums text-emerald-600">
                                {formatIDR(totalProfit)}
                            </p>
                        )}
                        <p className="text-xs text-muted-foreground">dari semua simpanan</p>
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
                            <Archive />
                        </EmptyMedia>
                        <EmptyTitle>Tidak ada simpanan</EmptyTitle>
                        <EmptyDescription>
                            Ubah filter pencarianmu atau tambah simpanan baru.
                        </EmptyDescription>
                    </Empty>
                )}

                {!loading &&
                    filtered.map((saving) => (
                        <Card key={saving.id}>
                            <CardContent className="space-y-3 p-4">
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
                                            {formatFullDate(saving.date)}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-xl font-semibold tabular-nums">
                                            {formatIDR(saving.balance)}
                                        </p>
                                        <p className="text-xs text-muted-foreground">saldo</p>
                                    </div>
                                </div>

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
                                        </dd>
                                    </div>
                                </dl>

                                <div className="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="xs"
                                        onClick={() => navigate(`/savings/${saving.id}`)}
                                    >
                                        Detail
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="xs"
                                        onClick={() => setDeleteTarget(saving)}
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
            </div>

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(open) => {
                    if (!open) setDeleteTarget(null);
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Hapus Simpanan</DialogTitle>
                    </DialogHeader>

                    <p className="mb-4 text-sm">
                        Anda yakin ingin menghapus simpanan ini? Beserta seluruh riwayat
                        transaksinya akan{" "}
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
