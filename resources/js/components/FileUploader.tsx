import { useRef } from "react";
import { FileTextIcon, ImageIcon, PaperclipIcon, PlusIcon, XIcon } from "lucide-react";
import { Attachment, AttachmentAction, AttachmentActions, AttachmentContent, AttachmentDescription, AttachmentMedia, AttachmentTitle } from "@/components/ui/attachment";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";

export interface FileEntry {
    file: File;
    url: string;
}

const MAX_FILES = 10;
const MAX_FILE_SIZE = 5 * 1024 * 1024;

export function createFileEntries(list: FileList | File[] | null): FileEntry[] {
    return Array.from(list ?? []).map((file) => ({
        file,
        url: file.type.startsWith("image/") ? URL.createObjectURL(file) : "",
    }));
}

export function revokeFileEntries(entries: FileEntry[]) {
    entries.forEach((entry) => {
        if (entry.url) URL.revokeObjectURL(entry.url);
    });
}

function formatFileSize(bytes: number) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function fileExt(name: string) {
    const index = name.lastIndexOf(".");
    return index > -1 ? name.slice(index + 1).toUpperCase() : "FILE";
}

interface FileUploaderProps {
    files: FileEntry[];
    onChange: (next: FileEntry[]) => void;
    onValidationError: (message: string) => void;
    error?: string;
}

export default function FileUploader({ files, onChange, onValidationError, error }: FileUploaderProps) {
    const inputRef = useRef<HTMLInputElement>(null);

    function handleFiles(list: FileList | null) {
        const selected = Array.from(list ?? []);
        if (selected.length > MAX_FILES) {
            onValidationError(`Maksimal ${MAX_FILES} file.`);
            return;
        }
        const tooBig = selected.find((file) => file.size > MAX_FILE_SIZE);
        if (tooBig) {
            onValidationError(`File "${tooBig.name}" melebihi 5mb.`);
            return;
        }
        onValidationError("");
        revokeFileEntries(files);
        onChange(createFileEntries(selected));
    }

    function removeFile(index: number) {
        const entry = files[index];
        if (entry?.url) URL.revokeObjectURL(entry.url);
        onChange(files.filter((_, i) => i !== index));
    }

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between gap-3">
                <Label htmlFor="attachments" className="text-sm font-medium">
                    Lampiran{" "}
                    <span className="font-normal text-muted-foreground">
                        (opsional, maks 10 file / 5mb)
                    </span>
                </Label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => inputRef.current?.click()}
                >
                    <PlusIcon /> Pilih file
                </Button>
            </div>

            <input
                id="attachments"
                ref={inputRef}
                type="file"
                multiple
                className="hidden"
                onChange={(e) => {
                    handleFiles(e.target.files);
                    e.target.value = "";
                }}
            />

            {files.length > 0 ? (
                <div className="flex flex-col gap-2">
                    {files.map((entry, index) => {
                        const isImage = !!entry.url;
                        const isPdf = entry.file.type === "application/pdf";

                        return (
                            <Attachment
                                key={`${entry.file.name}-${entry.file.lastModified}-${index}`}
                                size="sm"
                                state="idle"
                                className="w-full max-w-full"
                            >
                                <AttachmentMedia variant={isImage ? "image" : "icon"}>
                                    {isImage ? (
                                        <img src={entry.url} alt={entry.file.name} />
                                    ) : isPdf ? (
                                        <FileTextIcon />
                                    ) : (
                                        <PaperclipIcon />
                                    )}
                                </AttachmentMedia>
                                <AttachmentContent>
                                    <AttachmentTitle>{entry.file.name}</AttachmentTitle>
                                    <AttachmentDescription>
                                        {fileExt(entry.file.name)} · {formatFileSize(entry.file.size)} · siap
                                        diunggah
                                    </AttachmentDescription>
                                </AttachmentContent>
                                <AttachmentActions>
                                    <AttachmentAction
                                        aria-label={`Hapus ${entry.file.name}`}
                                        onClick={() => removeFile(index)}
                                    >
                                        <XIcon />
                                    </AttachmentAction>
                                </AttachmentActions>
                            </Attachment>
                        );
                    })}
                </div>
            ) : (
                <div className="flex items-center gap-3 border border-dashed p-4 text-sm text-muted-foreground">
                    <ImageIcon className="size-4 shrink-0" />
                    <span>Belum ada lampiran. Gambar (jpg/png) atau PDF, maks 5mb.</span>
                </div>
            )}

            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
