import { Archive, ArrowLeftRight, ArrowUpRight, Banknote, Clock, Plus } from "lucide-react";
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from "@/components/ui/chart";
import { Empty, EmptyDescription, EmptyMedia, EmptyTitle } from "@/components/ui/empty";
import { Skeleton } from "@/components/ui/skeleton";
import { api } from "@/lib/api";
import { formatIDR } from "@/lib/format";
import { usePageTitle } from "@/lib/usePageTitle";
import type { DashboardSummary } from "@/lib/types";
import { Pie, PieChart } from "recharts";

interface StatProps {
    label: string;
    value: string;
    icon: React.ComponentType<{ className?: string }>;
    tone: string;
}

function Stat({ label, value, icon: Icon, tone }: StatProps) {
    return (
        <Card>
            <CardContent className="flex flex-col gap-3 p-4">
                <div className={`flex size-9 items-center justify-center rounded-md ${tone}`}>
                    <Icon className="size-4.5" />
                </div>
                <div className="min-w-0">
                    <p className="text-xs font-medium text-muted-foreground">{label}</p>
                    <p className="truncate text-xl font-semibold tabular-nums">{value}</p>
                </div>
            </CardContent>
        </Card>
    );
}

const chartConfig = {
    value: { label: "Piutang" },
    paid: { label: "Sudah Dibayar", color: "var(--chart-1)" },
    outstanding: { label: "Belum Dibayar", color: "var(--destructive)" },
} satisfies ChartConfig;

const shortcuts = [
    { href: "/loans", icon: Banknote, label: "Pinjaman", tone: "bg-emerald-500/10 text-emerald-600" },
    { href: "/savings", icon: Archive, label: "Simpanan", tone: "bg-indigo-500/10 text-indigo-600" },
    {
        href: "/choose",
        icon: Plus,
        label: "Tambah Data",
        tone: "bg-blue-500/10 text-blue-600",
        className: "hidden sm:block",
    },
];

export default function Home() {
    usePageTitle("Home");
    const [summary, setSummary] = useState<DashboardSummary | null>(null);

    useEffect(() => {
        api.get("/dashboard/summary")
            .then((res) => setSummary(res.data.data as DashboardSummary))
            .catch((err) => console.error(err));
    }, []);

    const loading = summary === null;
    const paid = summary ? Math.max(summary.total_lent - summary.loan_outstanding, 0) : 0;
    const outstanding = summary?.loan_outstanding ?? 0;
    const hasLoanData = (summary?.total_lent ?? 0) > 0;

    const chartData = [
        { name: "paid", value: paid, fill: "var(--color-paid)" },
        { name: "outstanding", value: outstanding, fill: "var(--color-outstanding)" },
    ];

    return (
        <div>
            <h1 className="mb-4 text-2xl font-bold">Home</h1>

            <div className="space-y-6">
                <div className="grid grid-cols-2 gap-3">
                    {loading ? (
                        Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-[118px] w-full" />)
                    ) : (
                        <>
                            <Stat
                                label="Uang Masuk / Keluar"
                                value={formatIDR(summary!.cash_flow_net)}
                                icon={ArrowLeftRight}
                                tone="bg-blue-500/10 text-blue-600"
                            />
                            <Stat
                                label="Total Meminjamkan"
                                value={formatIDR(summary!.total_lent)}
                                icon={Banknote}
                                tone="bg-emerald-500/10 text-emerald-600"
                            />
                            <Stat
                                label="Belum Dibayar Peminjam"
                                value={formatIDR(summary!.loan_outstanding)}
                                icon={Clock}
                                tone="bg-red-500/10 text-red-600"
                            />
                            <Stat
                                label="Saldo Simpanan"
                                value={formatIDR(summary!.savings_balance)}
                                icon={Archive}
                                tone="bg-indigo-500/10 text-indigo-600"
                            />
                        </>
                    )}
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Status Piutang</CardTitle>
                        <CardDescription>Perbandingan pinjaman yang sudah dan belum dibayar</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {loading ? (
                            <Skeleton className="mx-auto h-[200px] w-full max-w-[300px] rounded-md" />
                        ) : hasLoanData ? (
                            <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                                <ChartContainer
                                    config={chartConfig}
                                    className="mx-auto aspect-auto h-[200px] w-full max-w-[300px]"
                                >
                                    <PieChart>
                                        <ChartTooltip content={<ChartTooltipContent />} />
                                        <Pie
                                            data={chartData}
                                            dataKey="value"
                                            nameKey="name"
                                            innerRadius={55}
                                            outerRadius={85}
                                            paddingAngle={2}
                                            stroke="none"
                                        />
                                    </PieChart>
                                </ChartContainer>

                                <div className="w-full max-w-[260px] space-y-3">
                                    {[
                                        { label: "Sudah Dibayar", value: paid, color: "var(--chart-1)" },
                                        { label: "Belum Dibayar", value: outstanding, color: "var(--destructive)" },
                                    ].map((item) => (
                                        <div key={item.label} className="flex items-center gap-2.5">
                                            <span
                                                className="size-2.5 shrink-0 rounded-full"
                                                style={{ background: item.color }}
                                            />
                                            <span className="flex-1 text-sm text-muted-foreground">{item.label}</span>
                                            <span className="text-sm font-semibold tabular-nums">
                                                {formatIDR(item.value)}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <Empty className="border-0 p-4">
                                <EmptyMedia variant="icon">
                                    <Banknote />
                                </EmptyMedia>
                                <EmptyTitle>Belum ada piutang</EmptyTitle>
                                <EmptyDescription>
                                    Pinjaman yang kamu buat akan muncul status pembayarannya di sini.
                                </EmptyDescription>
                            </Empty>
                        )}
                    </CardContent>
                </Card>

                <div>
                    <h2 className="mb-3 text-lg font-semibold">Lihat Selengkapnya</h2>
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        {shortcuts.map((shortcut) => (
                            <Link
                                key={shortcut.href}
                                to={shortcut.href}
                                className={`group block rounded-none border bg-card p-4 text-center transition-colors hover:bg-muted/50 ${shortcut.className ?? ""}`}
                            >
                                <div
                                    className={`mx-auto mb-2 flex size-9 items-center justify-center rounded-md ${shortcut.tone}`}
                                >
                                    <shortcut.icon className="size-4.5" />
                                </div>
                                <span className="block text-sm font-medium">{shortcut.label}</span>
                                <ArrowUpRight className="mx-auto mt-1 size-3.5 text-muted-foreground transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
