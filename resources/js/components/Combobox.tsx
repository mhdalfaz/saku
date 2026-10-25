import {
    Combobox as ComboboxPrimitive,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from "@/components/ui/combobox";

export interface ComboboxOption {
    id: number;
    name: string;
}

interface ComboboxProps {
    id?: string;
    placeholder: string;
    value: number | "";
    options: ComboboxOption[];
    onChange: (id: number | "") => void;
}

export default function Combobox({
    id,
    placeholder,
    value,
    options,
    onChange,
}: ComboboxProps) {
    const selected = options.find((option) => option.id === value) ?? null;

    return (
        <ComboboxPrimitive
            items={options}
            itemToStringValue={(option) => option.name}
            itemToStringLabel={(option) => option.name}
            isItemEqualToValue={(a, b) => a?.id === b?.id}
            value={selected}
            onValueChange={(option) => onChange(option ? option.id : "")}
        >
            <ComboboxInput id={id} placeholder={placeholder} className="w-full" />
            <ComboboxContent>
                <ComboboxEmpty>Tidak ada pilihan.</ComboboxEmpty>
                <ComboboxList>
                    {(option) => (
                        <ComboboxItem key={option.id} value={option}>
                            {option.name}
                        </ComboboxItem>
                    )}
                </ComboboxList>
            </ComboboxContent>
        </ComboboxPrimitive>
    );
}
