import { isAxiosError } from "axios";
import { useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import Breadcrumbs from "@/components/layout/Breadcrumbs";
import BackButton from "@/components/layout/BackButton";
import CustodianInput from "@/components/CustodianInput";
import FileUploader, { type FileEntry } from "@/components/FileUploader";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { api } from "@/lib/api";
import { formatIDR, unformatIDR } from "@/lib/format";
import { usePageTitle } from "@/lib/usePageTitle";

function cleanField(field: string) {
    return field.replace(/\.\d+$/, "");
}

export default function SavingCreate() {
    usePageTitle("Tambah Simpanan");
    const navigate = useNavigate();

    const [custodianId, setCustodianId] = useState<number | "">("");
    const [amount, setAmount] = useState("");
    const [date, setDate] = useState("");
    const [note, setNote] = useState("");
    const [files, setFiles] = useState<FileEntry[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [submitting, setSubmitting] = useState(false);

    async function handleSubmit(e: FormEvent) {
        e.preventDefault();
        if (submitting) return;

        setSubmitting(true);
        setErrors({});

        const formData = new FormData();
        formData.append("custodian_id", String(custodianId));
        formData.append("amount", String(parseInt(String(unformatIDR(amount)), 10)));
        formData.append("date", date);
        formData.append("note", note);
        files.forEach((entry) => formData.append("attachments[]", entry.file));

        try {
            await api.post("/savings", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            alert("Simpanan berhasil ditambahkan!");
            navigate("/savings");
        } catch (err) {
            if (isAxiosError(err)) {
                const data = err.response?.data as
                    | { message?: string; errors?: Record<string, string[]> }
                    | undefined;

                if (data?.errors) {
                    const mapped: Record<string, string> = {};
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        mapped[cleanField(field)] = messages[0];
                    });
                    setErrors(mapped);
                } else {
                    alert(data?.message || "Terjadi kesalahan, coba lagi.");
                }
            } else {
                alert("Terjadi kesalahan, coba lagi.");
            }
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <div>
            <div className="mb-1 flex items-center gap-2">
                <BackButton />
                <h1 className="text-2xl font-bold">Tambah Simpanan</h1>
            </div>
            <Breadcrumbs />

            <form onSubmit={handleSubmit} className="space-y-5 pb-32">
                <CustodianInput
                    value={custodianId}
                    onChange={(id) => {
                        setCustodianId(id);
                        setErrors((prev) => ({ ...prev, custodian_id: "" }));
                    }}
                    error={errors.custodian_id}
                />

                <Card>
                    <CardContent className="space-y-1">
                        <label className="block text-sm font-medium mb-1" htmlFor="amount">
                            Setoran Awal (IDR)
                        </label>
                        <Input
                            id="amount"
                            name="amount"
                            type="text"
                            placeholder="1000000"
                            required
                            value={amount}
                            onChange={(e) => setAmount(formatIDR(e.target.value))}
                        />
                        {errors.amount && (
                            <p className="text-red-600 text-sm">{errors.amount}</p>
                        )}

                        <label className="block text-sm font-medium mb-1 mt-3" htmlFor="date">
                            Tanggal Simpanan
                        </label>
                        <Input
                            id="date"
                            name="date"
                            type="date"
                            required
                            value={date}
                            onChange={(e) => setDate(e.target.value)}
                        />
                        {errors.date && (
                            <p className="text-red-600 text-sm">{errors.date}</p>
                        )}

                        <label className="block text-sm font-medium mb-1 mt-3" htmlFor="note">
                            Catatan (opsional)
                        </label>
                        <Textarea
                            id="note"
                            name="note"
                            placeholder="Misal: Simpanan investasi di..."
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                        />
                        {errors.note && (
                            <p className="text-red-600 text-sm">{errors.note}</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <FileUploader
                            files={files}
                            onChange={setFiles}
                            onValidationError={(message) =>
                                setErrors((prev) => ({ ...prev, attachments: message }))
                            }
                            error={errors.attachments}
                        />
                    </CardContent>
                </Card>

                <div className="fixed bottom-0 left-0 w-full bg-white py-6 px-6 border-t shadow-md z-40">
                    <div className="max-w-xl mx-auto">
                        <Button type="submit" className="w-full" disabled={submitting}>
                            {submitting ? "Menyimpan..." : "Tambah Simpanan"}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    );
}
