import { isAxiosError } from "axios";
import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import Breadcrumbs from "@/components/layout/Breadcrumbs";
import BackButton from "@/components/layout/BackButton";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { api } from "@/lib/api";
import { formatIDR, unformatIDR } from "@/lib/format";
import type { Loan } from "@/lib/types";
import { usePageTitle } from "@/lib/usePageTitle";
import { LoanProgress } from "./LoanList";

type TabKey = "single-pay" | "all-pays";

function formatDateShort(dateStr: string) {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

function formatDateLong(dateStr: string) {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
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

function rawFromInput(value: string) {
    const raw = unformatIDR(value);
    return Number.isNaN(raw) ? 0 : raw;
}

interface SinglePayProps {
    loan: Loan;
    loanId: number;
}

function SinglePay({ loan, loanId }: SinglePayProps) {
    const navigate = useNavigate();

    const paid = loan.paid_amount ?? loan.paid;
    const remaining = loan.remaining_amount ?? loan.remaining;

    const [amount, setAmount] = useState("");
    const [clientError, setClientError] = useState("");
    const [serverError, setServerError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    const raw = rawFromInput(amount);
    const afterRemaining = Math.max(remaining - raw, 0);

    function handleChange(value: string) {
        const next = rawFromInput(value);
        setServerError("");
        setClientError(next > remaining ? `Pembayaran tidak boleh melebihi ${formatIDR(remaining)}` : "");
        setAmount(next === 0 ? "" : formatIDR(next));
    }

    async function handlePay(e: FormEvent) {
        e.preventDefault();
        if (submitting) return;

        setSubmitting(true);
        setServerError("");
        setClientError("");

        try {
            await api.post(`/loans/${loanId}/pay`, { amount: raw });
            alert("Pembayaran berhasil!");
            navigate("/loans");
        } catch (err) {
            if (isAxiosError(err)) {
                const data = err.response?.data as
                    | { message?: string; errors?: Record<string, string[]> }
                    | undefined;
                if (data?.errors?.amount) {
                    setServerError(data.errors.amount[0]);
                } else {
                    setServerError(data?.message || "Terjadi kesalahan");
                }
            } else {
                setServerError("Terjadi kesalahan");
            }
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <form onSubmit={handlePay} className="pb-32">
            <div className="mb-4">
                <Card>
                    <CardContent>
                        <div className="flex justify-between mb-1">
                            <div className="font-bold text-lg">
                                {loan.borrower.name}
                            </div>
                            <div className="text-gray-500 text-sm">
                                {formatDateLong(loan.date)}
                            </div>
                        </div>

                        <div className="text-xl font-bold mb-1">
                            Total: {formatIDR(loan.total_amount)}
                        </div>

                        <div className="text-green-600 text-sm">
                            Sudah dibayar: {formatIDR(paid)}
                        </div>
                        <div className="text-red-600 text-sm mb-2">
                            Sisa: {formatIDR(remaining)}
                        </div>

                        <LoanProgress percent={loan.percent} />
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardContent>
                    <label className="block text-sm font-semibold mb-1" htmlFor="payAmount">
                        Nominal Pembayaran
                    </label>
                    <Input
                        id="payAmount"
                        type="text"
                        placeholder="Masukkan nominal"
                        value={amount}
                        onChange={(e) => handleChange(e.target.value)}
                    />

                    {serverError && (
                        <p className="text-red-600 text-sm mt-1">{serverError}</p>
                    )}
                    {clientError && (
                        <p className="text-red-500 text-sm mt-1">{clientError}</p>
                    )}

                    <div className="mt-4 text-sm">
                        <div>Sisa setelah bayar:</div>
                        <div className="font-bold text-lg">
                            {formatIDR(afterRemaining)}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div className="fixed bottom-0 left-0 w-full bg-white py-6 px-6 border-t shadow-md z-40">
                <div className="max-w-xl mx-auto">
                    <Button type="submit" className="w-full" disabled={submitting}>
                        {submitting ? "Memproses..." : "Bayar Sekarang"}
                    </Button>
                </div>
            </div>
        </form>
    );
}

interface BulkPayProps {
    loan: Loan;
    allLoans: Loan[];
    loadError: string;
    reload: () => Promise<void>;
}

function BulkPay({ loan, allLoans, loadError, reload }: BulkPayProps) {
    const navigate = useNavigate();

    const [amount, setAmount] = useState("");
    const [error, setError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    const totalAmount = allLoans.reduce((sum, l) => sum + l.total_amount, 0);
    const totalPaid = allLoans.reduce(
        (sum, l) => sum + (l.paid_amount ?? l.paid),
        0,
    );
    const totalRemaining = allLoans.reduce(
        (sum, l) => sum + (l.remaining_amount ?? l.remaining),
        0,
    );
    const percent = totalAmount > 0 ? Math.floor((totalPaid / totalAmount) * 100) : 0;

    const raw = rawFromInput(amount);
    const afterRemaining = Math.max(totalRemaining - raw, 0);

    function handleChange(value: string) {
        const next = rawFromInput(value);
        setError(
            next > totalRemaining
                ? `Pembayaran tidak boleh melebihi ${formatIDR(totalRemaining)}`
                : "",
        );
        setAmount(next === 0 ? "" : formatIDR(next));
    }

    async function handlePay(e: FormEvent) {
        e.preventDefault();
        if (submitting) return;

        if (!raw || raw <= 0) {
            alert("Masukkan nominal pembayaran");
            return;
        }
        if (raw > totalRemaining) {
            setError(
                `Pembayaran tidak boleh melebihi ${formatIDR(totalRemaining)}`,
            );
            return;
        }

        setSubmitting(true);
        setError("");

        let remainingAmount = raw;
        let successCount = 0;

        for (const l of allLoans) {
            if (remainingAmount <= 0) break;
            const loanRemaining = l.remaining_amount ?? l.remaining;
            if (loanRemaining <= 0) continue;

            const payAmountVal = Math.min(remainingAmount, loanRemaining);

            try {
                await api.post(`/loans/${l.id}/pay`, { amount: payAmountVal });
                successCount++;
            } catch (err) {
                console.error(err);
            }

            remainingAmount -= payAmountVal;
        }

        setSubmitting(false);

        if (successCount > 0) {
            alert(`Pembayaran berhasil! (${successCount} pinjaman)`);
            await reload();
            navigate("/loans");
        } else {
            alert("Gagal melakukan pembayaran");
        }
    }

    return (
        <div>
            <div className="mb-4">
                <Card>
                    <CardContent>
                        <div className="flex justify-between mb-1">
                            <div className="font-bold text-lg">
                                {loan.borrower.name}
                            </div>
                            <div className="text-gray-500 text-sm">
                                {allLoans.length} pinjaman
                            </div>
                        </div>

                        <div className="text-xl font-bold mb-1">
                            Total Pinjaman: {formatIDR(totalAmount)}
                        </div>

                        <div className="text-green-600 text-sm">
                            Sudah dibayar: {formatIDR(totalPaid)}
                        </div>
                        <div className="text-red-600 text-sm mb-2">
                            Total Sisa: {formatIDR(totalRemaining)}
                        </div>

                        <LoanProgress percent={percent} />
                    </CardContent>
                </Card>
            </div>

            <div className="space-y-3 mb-4">
                {loadError ? (
                    <div className="text-center py-8 text-red-500">
                        <p>Gagal memuat data</p>
                    </div>
                ) : allLoans.length === 0 ? (
                    <div className="text-center py-8 text-gray-500">
                        <p>Tidak ada data pinjaman</p>
                    </div>
                ) : (
                    allLoans.map((l) => (
                        <div
                            key={l.id}
                            className="bg-white border border-gray-300 p-3 flex justify-between items-center"
                        >
                            <div>
                                <div className="font-medium text-sm">
                                    {formatDateShort(l.date)}
                                </div>
                                <div className="text-xs text-gray-500">
                                    Sisa: {formatIDR(l.remaining_amount ?? l.remaining)}
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="font-bold">
                                    {formatIDR(l.total_amount)}
                                </div>
                                <div className="text-xs text-green-600">
                                    {l.percent}% lunas
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            <form onSubmit={handlePay} className="pb-32">
                <Card>
                    <CardContent>
                        <label className="block text-sm font-semibold mb-1" htmlFor="payAmountAll">
                            Nominal Pembayaran
                        </label>
                        <Input
                            id="payAmountAll"
                            type="text"
                            placeholder="Masukkan nominal"
                            value={amount}
                            onChange={(e) => handleChange(e.target.value)}
                        />

                        {error && <p className="text-red-500 text-sm mt-1">{error}</p>}

                        <div className="mt-4 text-sm">
                            <div>Sisa setelah bayar:</div>
                            <div className="font-bold text-lg">
                                {formatIDR(afterRemaining)}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="fixed bottom-0 left-0 w-full bg-white py-6 px-6 border-t shadow-md z-40">
                    <div className="max-w-xl mx-auto">
                        <Button type="submit" className="w-full" disabled={submitting}>
                            {submitting ? "Memproses..." : "Bayar Sekarang"}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    );
}

export default function LoanPay() {
    usePageTitle("Bayar Pinjaman");
    const { loanId } = useParams<{ loanId: string }>();

    const [loan, setLoan] = useState<Loan | null>(null);
    const [allLoans, setAllLoans] = useState<Loan[]>([]);
    const [allLoansError, setAllLoansError] = useState("");
    const [activeTab, setActiveTab] = useState<TabKey>("single-pay");
    const [loadFailed, setLoadFailed] = useState(false);

    const loadLoan = async () => {
        try {
            const res = await api.get(`/loans/${loanId}`);
            const data = res.data.data as Loan;
            setLoan(data);

            try {
                const allRes = await api.get(
                    `/loans/borrower/${data.borrower_id}?status=ongoing`,
                );
                setAllLoans((allRes.data.data ?? []) as Loan[]);
                setAllLoansError("");
            } catch (err) {
                console.error(err);
                setAllLoansError("Gagal memuat data");
            }
        } catch (err) {
            console.error(err);
            setLoadFailed(true);
        }
    };

    useEffect(() => {
        loadLoan();
    }, [loanId]);

    if (loadFailed) {
        return (
            <div>
                <div className="mb-1 flex items-center gap-2">
                    <BackButton />
                    <h1 className="text-2xl font-bold">Bayar Pinjaman</h1>
                </div>
                <Breadcrumbs />
                <p className="text-sm text-destructive">Gagal memuat data pinjaman</p>
            </div>
        );
    }

    if (!loan) {
        return (
            <div>
                <div className="mb-1 flex items-center gap-2">
                    <BackButton />
                    <h1 className="text-2xl font-bold">Bayar Pinjaman</h1>
                </div>
                <Breadcrumbs />
                <div className="flex justify-center py-10">
                    <div className="h-8 w-8 animate-spin border-2 border-gray-300 border-t-black" />
                </div>
            </div>
        );
    }

    const tabs: { key: TabKey; label: string }[] = [
        { key: "single-pay", label: "Bayar Pinjaman" },
        { key: "all-pays", label: "Bayar Semua Pinjaman" },
    ];

    return (
        <div>
            <div className="mb-1 flex items-center gap-2">
                <BackButton />
                <h1 className="text-2xl font-bold">Bayar Pinjaman</h1>
            </div>
            <Breadcrumbs />

            <div className="flex gap-2 p-2 bg-slate-200 rounded-none border border-slate-200 w-full">
                {tabs.map((tab) => (
                    <button
                        key={tab.key}
                        type="button"
                        onClick={() => setActiveTab(tab.key)}
                        className={`flex flex-col items-center justify-center px-[18px] py-3 rounded-none border-none text-sm font-medium w-full cursor-pointer transition-all duration-200 ${
                            activeTab === tab.key
                                ? "bg-white text-gray-900"
                                : "bg-transparent text-gray-500 hover:bg-white/50 hover:text-gray-700"
                        }`}
                    >
                        <span>{tab.label}</span>
                    </button>
                ))}
            </div>

            <div className="mt-4">
                {activeTab === "single-pay" ? (
                    <SinglePay loan={loan} loanId={loan.id} />
                ) : (
                    <BulkPay
                        loan={loan}
                        allLoans={allLoans}
                        loadError={allLoansError}
                        reload={loadLoan}
                    />
                )}
            </div>
        </div>
    );
}
