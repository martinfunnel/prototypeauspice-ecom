import { ORDER_STATUS_LABELS } from "@/lib/format";

export function StatusBadge({ status }: { status: string }) {
  const meta = ORDER_STATUS_LABELS[status] ?? { label: status, color: "bg-muted text-muted-foreground border-border" };
  return (
    <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${meta.color}`}>
      {meta.label}
    </span>
  );
}
