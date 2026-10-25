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
import type { Custodian } from "@/lib/types";

interface CustodianInputProps {
    value: number | "";
    onChange: (id: number | "") => void;
    error?: string;
}

export default function CustodianInput({ value, onChange, error }: CustodianInputProps) {
    const [custodians, setCustodians] = useState<Custodian[]>([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [saving, setSaving] = useState(false);
    const [formError, setFormError] = useState("");
    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [phone, setPhone] = useState("");
    const [address, setAddress] = useState("");

    async function loadCustodians() {
        try {
            const res = await api.get("/custodians");
            setCustodians((res.data.data ?? []) as Custodian[]);
        } catch (err) {
            console.error(err);
        }
    }

    useEffect(() => {
        loadCustodians();
    }, []);

    async function handleSave(e: FormEvent) {
        e.preventDefault();
        if (name.trim() === "") {
            setFormError("Nama penampung tidak boleh kosong.");
            return;
        }

        setSaving(true);
        setFormError("");

        try {
            const payload: Record<string, string> = { name: name.trim() };
            if (email.trim()) payload.email = email.trim();
            if (phone.trim()) payload.phone = phone.trim();
            if (address.trim()) payload.address = address.trim();

            const res = await api.post("/custodians", payload);
            const created = res.data.data as Custodian;

            await loadCustodians();
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
                    setFormError(data?.message || "Gagal menambahkan penampung.");
                }
            } else {
                setFormError("Terjadi kesalahan saat menambahkan penampung.");
            }
        } finally {
            setSaving(false);
        }
    }

    return (
        <>
            <Card>
                <CardContent>
                    <Label htmlFor="custodian_id" className="mb-1 block">
                        Penampung
                    </Label>

                    <div className="flex items-start gap-2">
                        <div className="flex-1">
                            <Combobox
                                id="custodian_id"
                                placeholder="Pilih penampung"
                                value={value}
                                options={custodians.map((c) => ({ id: c.id, name: c.name }))}
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
                            aria-label="Tambah penampung"
                        >
                            <Plus />
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Dialog open={modalOpen} onOpenChange={setModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Penampung</DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleSave} className="space-y-3">
                        {formError && (
                            <p className="text-sm text-red-600">{formError}</p>
                        )}

                        <div>
                            <Label htmlFor="custodianName">
                                Nama Penampung
                            </Label>
                            <Input
                                id="custodianName"
                                placeholder="Masukkan nama"
                                required
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                            />
                        </div>

                        <div>
                            <Label htmlFor="custodianEmail">
                                Email
                            </Label>
                            <Input
                                id="custodianEmail"
                                type="email"
                                placeholder="Masukkan email"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                            />
                        </div>

                        <div>
                            <Label htmlFor="custodianPhone">
                                Telepon
                            </Label>
                            <Input
                                id="custodianPhone"
                                placeholder="Masukkan nomor telepon"
                                value={phone}
                                onChange={(e) => setPhone(e.target.value)}
                            />
                        </div>

                        <div>
                            <Label htmlFor="custodianAddress">
                                Alamat
                            </Label>
                            <Input
                                id="custodianAddress"
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
