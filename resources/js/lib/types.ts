export interface ApiResponse<T = unknown> {
    success: boolean;
    message: string;
    data: T;
    errors?: Record<string, string[]> | null;
}

export interface User {
    id: number;
    name: string;
    email: string;
    created_at?: string;
    updated_at?: string;
}

export interface AuthTokenPayload {
    token: string;
    token_type: string;
    expires_in: number;
}

export interface DashboardSummary {
    cash_flow_net: number;
    total_lent: number;
    loan_outstanding: number;
    savings_balance: number;
}

export interface Custodian {
    id: number;
    user_id: number;
    name: string;
    email?: string | null;
    phone?: string | null;
    address?: string | null;
    avatar: string;
}

export type SavingStatus = "ongoing" | "closed";
export type SavingTransactionType = "deposit" | "withdrawal" | "profit";

export interface SavingTransaction {
    id: number;
    saving_id: number;
    type: SavingTransactionType;
    retained: boolean | null;
    amount: number;
    note: string | null;
    date: string;
}

export interface Attachment {
    id: number;
    original_name: string;
    mime_type?: string | null;
    public_url: string;
}

export interface Saving {
    id: number;
    user_id: number;
    custodian_id: number;
    status: SavingStatus;
    date: string;
    description: string | null;
    custodian: Custodian;
    transactions: SavingTransaction[];
    images: Attachment[];
    files: Attachment[];
    total_deposit: number;
    total_withdrawal: number;
    total_profit: number;
    profit_retained: number;
    balance: number;
}

export interface Borrower {
    id: number;
    name: string;
    email?: string | null;
    phone?: string | null;
    address?: string | null;
    avatar: string;
}

export type LoanStatus = "ongoing" | "paid";

export interface LoanTransaction {
    id: number;
    loan_id: number;
    amount: number;
    note: string | null;
    date: string;
}

export interface Loan {
    id: number;
    user_id: number;
    borrower_id: number;
    total_amount: number;
    date: string;
    due_date?: string | null;
    description: string | null;
    status: LoanStatus;
    borrower: Borrower;
    transactions: LoanTransaction[];
    images: Attachment[];
    files: Attachment[];
    paid: number;
    remaining: number;
    percent: number;
    paid_amount?: number;
    remaining_amount?: number;
}
