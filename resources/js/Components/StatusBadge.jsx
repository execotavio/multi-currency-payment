const styles = {
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    approved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-rose-100 text-rose-800 border-rose-200',
    expired: 'bg-zinc-100 text-zinc-700 border-zinc-200',
};

export default function StatusBadge({ status }) {
    return (
        <span className={`inline-flex items-center rounded-md border px-2 py-1 text-xs font-medium ${styles[status] ?? styles.pending}`}>
            {status}
        </span>
    );
}
