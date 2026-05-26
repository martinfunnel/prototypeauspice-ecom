import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
} from "recharts";
import { AdminShell } from "@/components/AdminShell";
import { getAdminStats } from "@/lib/admin.functions";
import { formatCFA } from "@/lib/format";
import { TrendingUp, ShoppingBag, Clock, CheckCircle2, Package, AlertTriangle } from "lucide-react";

export const Route = createFileRoute("/admin/")({
  head: () => ({ meta: [{ title: "Dashboard admin — Santé Ivoire" }] }),
  component: AdminDashboard,
});

function StatCard({
  label,
  value,
  icon: Icon,
  tone = "primary",
}: {
  label: string;
  value: string;
  icon: React.ComponentType<{ className?: string }>;
  tone?: "primary" | "accent" | "success" | "warning";
}) {
  const tones: Record<string, string> = {
    primary: "bg-primary/10 text-primary",
    accent: "bg-accent/15 text-accent",
    success: "bg-success/15 text-success",
    warning: "bg-warning/15 text-warning-foreground",
  };
  return (
    <div className="rounded-2xl border border-border bg-card p-4 shadow-card">
      <div className="flex items-start justify-between gap-2">
        <div>
          <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
            {label}
          </div>
          <div className="mt-1 font-display text-2xl font-bold">{value}</div>
        </div>
        <div className={`grid h-10 w-10 place-items-center rounded-xl ${tones[tone]}`}>
          <Icon className="h-5 w-5" />
        </div>
      </div>
    </div>
  );
}

function AdminDashboard() {
  const fn = useServerFn(getAdminStats);
  const { data, isLoading } = useQuery({ queryKey: ["admin-stats"], queryFn: () => fn() });

  return (
    <AdminShell title="Tableau de bord">
      {isLoading || !data ? (
        <div className="text-sm text-muted-foreground">Chargement des statistiques…</div>
      ) : (
        <div className="space-y-6">
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard label="CA total" value={formatCFA(data.revenue)} icon={TrendingUp} tone="success" />
            <StatCard label="Commandes" value={String(data.ordersTotal)} icon={ShoppingBag} tone="primary" />
            <StatCard label="En attente" value={String(data.pending)} icon={Clock} tone="warning" />
            <StatCard label="Livrées" value={String(data.delivered)} icon={CheckCircle2} tone="success" />
          </div>

          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard
              label="Produits actifs"
              value={`${data.productsActive} / ${data.productsTotal}`}
              icon={Package}
              tone="primary"
            />
            <StatCard label="Stock faible" value={String(data.lowStock)} icon={AlertTriangle} tone="warning" />
            <StatCard label="Communes livrées" value={String(data.communesActive)} icon={CheckCircle2} tone="accent" />
          </div>

          <div className="rounded-2xl border border-border bg-card p-4 shadow-card">
            <div className="mb-3 flex items-center justify-between">
              <h2 className="font-display text-base font-bold">Activité 7 derniers jours</h2>
              <span className="text-xs text-muted-foreground">CA & nb de commandes</span>
            </div>
            <div className="h-64 w-full">
              <ResponsiveContainer>
                <AreaChart data={data.chart} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                  <defs>
                    <linearGradient id="rev" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor="hsl(var(--accent))" stopOpacity={0.5} />
                      <stop offset="100%" stopColor="hsl(var(--accent))" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis
                    dataKey="date"
                    tick={{ fontSize: 11 }}
                    tickFormatter={(d) => new Date(d).toLocaleDateString("fr-FR", { weekday: "short" })}
                  />
                  <YAxis tick={{ fontSize: 11 }} />
                  <Tooltip
                    formatter={(v: number, n: string) =>
                      n === "revenue" ? formatCFA(v) : `${v} cmd`
                    }
                    labelFormatter={(d) => new Date(d as string).toLocaleDateString("fr-FR")}
                  />
                  <Area
                    type="monotone"
                    dataKey="revenue"
                    stroke="hsl(var(--accent))"
                    fill="url(#rev)"
                    strokeWidth={2}
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>
      )}
    </AdminShell>
  );
}
