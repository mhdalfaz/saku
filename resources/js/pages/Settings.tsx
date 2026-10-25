import { LogOut, Settings2 } from "lucide-react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/auth/AuthContext";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Empty, EmptyDescription, EmptyMedia, EmptyTitle } from "@/components/ui/empty";
import { Separator } from "@/components/ui/separator";
import { Skeleton } from "@/components/ui/skeleton";
import { usePageTitle } from "@/lib/usePageTitle";

export default function Settings() {
    usePageTitle("Pengaturan");
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    async function handleLogout() {
        await logout();
        navigate("/login", { replace: true });
    }

    const initials = user
        ? user.name
              .split(" ")
              .slice(0, 2)
              .map((part) => part[0])
              .join("")
              .toUpperCase()
        : "";

    return (
        <div>
            <h1 className="mb-4 text-2xl font-bold">Pengaturan</h1>

            <div className="space-y-4">
                <Card>
                    <CardContent className="flex items-center gap-4 p-4">
                        {user ? (
                            <>
                                <Avatar className="size-12">
                                    <AvatarImage
                                        src={`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=0D0D0D&color=fff`}
                                        alt={user.name}
                                    />
                                    <AvatarFallback>{initials}</AvatarFallback>
                                </Avatar>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-semibold leading-tight">{user.name}</p>
                                    <p className="truncate text-sm text-muted-foreground">{user.email}</p>
                                </div>
                            </>
                        ) : (
                            <>
                                <Skeleton className="size-12 rounded-full" />
                                <div className="flex-1 space-y-2">
                                    <Skeleton className="h-4 w-32" />
                                    <Skeleton className="h-3 w-24" />
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Preferensi</CardTitle>
                        <CardDescription>Penyesuaian aplikasi untuk akunmu</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Empty className="border-0 p-4">
                            <EmptyMedia variant="icon">
                                <Settings2 />
                            </EmptyMedia>
                            <EmptyTitle>Belum ada pengaturan</EmptyTitle>
                            <EmptyDescription>
                                Opsi pengaturan akan tersedia di sini pada pembaruan berikutnya.
                            </EmptyDescription>
                        </Empty>
                    </CardContent>
                </Card>

                <Separator />

                <Button variant="destructive" className="w-full" onClick={handleLogout}>
                    <LogOut /> Logout
                </Button>

                <p className="pb-2 text-center text-xs text-muted-foreground">Saku</p>
            </div>
        </div>
    );
}
