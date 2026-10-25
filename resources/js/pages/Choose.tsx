import { Archive, ArrowLeftRight, ArrowRight, Banknote } from "lucide-react";
import { Link } from "react-router-dom";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { usePageTitle } from "@/lib/usePageTitle";

const features = [
    {
        href: "/loans/create",
        icon: Banknote,
        title: "Pinjaman",
        description: "Tambah pinjaman ke orang lain",
        tone: "bg-emerald-500/10 text-emerald-600",
    },
    {
        href: "/savings/create",
        icon: Archive,
        title: "Simpanan",
        description: "Catat uang yang disimpan di orang lain",
        tone: "bg-indigo-500/10 text-indigo-600",
    },
    {
        href: "/cash-flow/create",
        icon: ArrowLeftRight,
        title: "Cash Flow",
        description: "Tambah pemasukan atau pengeluaran",
        tone: "bg-blue-500/10 text-blue-600",
        comingSoon: true,
    },
];

export default function Choose() {
    usePageTitle("Tambah");

    return (
        <div>
            <div className="mb-4">
                <h1 className="text-2xl font-bold">Pilih Fitur yang Ingin Ditambahkan</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Data baru akan tersimpan ke akunmu.
                </p>
            </div>

            <div className="grid grid-cols-1 gap-3">
                {features.map((feature) => {
                    const content = (
                        <Card
                            className={
                                feature.comingSoon
                                    ? "opacity-60 transition"
                                    : "transition-colors hover:border-foreground/25 hover:bg-muted/50"
                            }
                        >
                            <CardContent className="flex items-center gap-4 p-4">
                                <div
                                    className={`flex size-11 shrink-0 items-center justify-center rounded-md ${feature.tone}`}
                                >
                                    <feature.icon className="size-5" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center gap-2">
                                        <h2 className="font-semibold">{feature.title}</h2>
                                        {feature.comingSoon && (
                                            <Badge variant="secondary" className="text-[10px]">
                                                Segera hadir
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="text-sm text-muted-foreground">{feature.description}</p>
                                </div>
                                {!feature.comingSoon && (
                                    <ArrowRight className="size-4 shrink-0 text-muted-foreground" />
                                )}
                            </CardContent>
                        </Card>
                    );

                    return feature.comingSoon ? (
                        <div key={feature.href} aria-disabled="true">
                            {content}
                        </div>
                    ) : (
                        <Link key={feature.href} to={feature.href} className="block">
                            {content}
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}
