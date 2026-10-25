import { isAxiosError } from "axios";
import { useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/auth/AuthContext";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { usePageTitle } from "@/lib/usePageTitle";

export default function Login() {
    usePageTitle("Login");
    const { login } = useAuth();
    const navigate = useNavigate();

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    const isValid = email.trim() !== "" && password !== "";

    async function handleSubmit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        if (!isValid || submitting) return;

        setSubmitting(true);
        setError("");

        try {
            await login(email, password);
            navigate("/home", { replace: true });
        } catch (err) {
            if (isAxiosError(err)) {
                const message = (err.response?.data as { message?: string } | undefined)
                    ?.message;
                setError(message || "Login gagal");
            } else {
                setError("Terjadi kesalahan");
            }
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <div className="flex min-h-[calc(100vh-3rem)] items-center justify-center">
            <Card className="w-full max-w-sm p-6">
                <h1 className="mb-4 text-center text-xl font-bold">Masuk ke Saku</h1>

                {error && (
                    <div className="mb-3 border border-destructive/20 bg-destructive/10 p-3 text-sm text-destructive">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <Label htmlFor="email" className="mb-1 block">
                            Email
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>

                    <div className="mb-4">
                        <Label htmlFor="password" className="mb-1 block">
                            Password
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={!isValid || submitting}
                    >
                        {submitting ? "Loading..." : "Login"}
                    </Button>
                </form>
            </Card>
        </div>
    );
}
