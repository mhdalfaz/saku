import { Fragment } from "react";
import { Link, useLocation } from "react-router-dom";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/components/ui/breadcrumb";

const LABELS: Record<string, string> = {
    home: "Home",
    choose: "Tambah",
    savings: "Simpanan",
    loans: "Pinjaman",
    settings: "Pengaturan",
    create: "Tambah",
    edit: "Ubah",
    pay: "Bayar",
};

function labelForSegment(segment: string) {
    if (/^\d+$/.test(segment)) return "Detail";
    return LABELS[segment] ?? segment;
}

export default function Breadcrumbs() {
    const { pathname } = useLocation();
    const segments = pathname.split("/").filter(Boolean);

    if (segments.length < 2) return null;

    const items = segments.map((segment, index) => ({
        label: labelForSegment(segment),
        href:
            index === segments.length - 1
                ? undefined
                : `/${segments.slice(0, index + 1).join("/")}`,
    }));

    return (
        <Breadcrumb className="mb-4">
            <BreadcrumbList className="flex-nowrap overflow-hidden">
                {items.map((item, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <Fragment key={`${item.label}-${index}`}>
                            {index > 0 && <BreadcrumbSeparator className="shrink-0" />}
                            <BreadcrumbItem className="min-w-0 shrink-0">
                                {isLast || !item.href ? (
                                    <BreadcrumbPage className="truncate">
                                        {item.label}
                                    </BreadcrumbPage>
                                ) : (
                                    <BreadcrumbLink
                                        render={<Link to={item.href} className="truncate" />}
                                    >
                                        {item.label}
                                    </BreadcrumbLink>
                                )}
                            </BreadcrumbItem>
                        </Fragment>
                    );
                })}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
