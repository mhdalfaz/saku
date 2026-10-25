import { isAxiosError } from "axios";
import { Plus } from "lucide-react";
import { useEffect, useState, type FormEvent } from "react";
import Combobox from "@/components/Combobox";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { api } from "@/lib/api";
import type { Borrower } from "@/lib/types";

interface BorrowerInputProps {
    value: number | "";
    onChange: (id: number | "") => void;
    error?: string;
}

export default function BorrowerInput({ value, onChange, error }: BorrowerInputProps) {
    const [borrowers, setBorrowers] = useState<Borrower[]>([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [saving, setSaving] = useState(false);
    const [formError, setFormError] = useState("");
    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [phone, setPhone] = useState("");
    const [address, setAddress] = useState("");

    async function loadBorrowers() {
        try {
            const res = await api.get("/borrowers");
            setBorrowers((res.data.data ?? []) as Borrower[]);
        } catch (err) {
            console.error(err);
        }
    }

    useEffect(() => {
        loadBorrowers();
    }, []);

    async function handleSave(e: FormEvent) {
        e.preventDefault();
        if (name.trim() === "") {
            setFormError("Nama peminjam tidak boleh kosong.");
            return;
        }

        setSaving(true);
        setFormError("");

        try {
            const payload: Record<string, string> = { name: name.trim() };
            if (email.trim()) payload.email = email.trim();
            if (phone.trim()) payload.phone = phone.trim();
            if (address.trim()) payload.address = address.trim();

            const res = await api.post("/borrowers", payload);
            const created = res.data.data as Borrower;

            await loadBorrowers();
            onChange(created.id);
            setModalOpen(false);
            setName("");
            setEmail("");
            setPhone("");
            setAddress("");
        } catch (err) {
            if (isAxiosError(err)) {
                const data = err.response?.data as
                    | { message?: string; errors?: Record<string, string[]> }
                    | undefined;
                if (data?.errors) {
                    setFormError(Object.values(data.errors)[0]?.[0] ?? data.message ?? "Gagal.");
                } else {
                    setFormError(data?.message || "Gagal menambahkan peminjam.");
                }
            } else {
                setFormError("Terjadi kesalahan saat menambahkan peminjam.");
            }
        } finally {
            setSaving(false);
        }
    }

    return (
        <>
            <Card>
                <CardContent>
                    <Label htmlFor="borrower_id" className="mb-1 block">
                        Peminjam
                    </Label>

                    <div className="flex items-start gap-2">
                        <div className="flex-1">
                            <Combobox
                                id="borrower_id"
                                placeholder="Pilih peminjam"
                                value={value}
                                options={borrowers.map((b) => ({ id: b.id, name: b.name }))}
                                onChange={onChange}
                            />
                            {error && (
                                <p className="mt-2 text-sm text-destructive">{error}</p>
                            )}
                        </div>

                        <Button
                            type="button"
                            size="icon"
                            onClick={() => setModalOpen(true)}
                            aria-label="Tambah peminjam"
                        >
                            <Plus />
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Dialog open={modalOpen} onOpenChange={setModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Peminjam</DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleSave} className="space-y-3">
                        {formError && (
                            <p className="text-sm text-red-600">{formError}</p>
                        )}

                        <div>
                            <Label htmlFor="borrowerName">
                                Nama Peminjam
                            </Label>
                            <Input
                                id="borrowerName"
                                placeholder="Masukkan nama"
                                required
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                            />
                        </div>

                        <div>
                            <Label htmlFor="borrowerEmail">
                                Email
                            </Label>
                            <Input
                                id="borrowerEmail"
                                type="email"
                                placeholder="Masukkan email"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                            />
                        </div>

                        <div>
                            <Label htmlFor="borrowerPhone">
                                Telepon
                            </Label>
                            <Input
                                id="borrowerPhone"
                                placeholder="Masukkan nomor telepon"
                                value={phone}
                                onChange={(e) => setPhone(e.target.value)}
                            />
                        </div>

                        <div>
                            <Label htmlFor="borrowerAddress">
                                Alamat
                            </Label>
                            <Input
                                id="borrowerAddress"
                                placeholder="Masukkan alamat"
                                value={address}
                                onChange={(e) => setAddress(e.target.value)}
                            />
                        </div>

                        <Button type="submit" className="w-full" disabled={saving}>
                            {saving ? "Menyimpan..." : "Simpan"}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
