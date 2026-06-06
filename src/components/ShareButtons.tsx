import { Facebook, Send, Share2, MessageCircle, Link as LinkIcon } from "lucide-react";
import { toast } from "sonner";

export function ShareButtons({ url, title }: { url: string; title: string }) {
  const enc = encodeURIComponent;
  const links = [
    {
      label: "WhatsApp",
      href: `https://wa.me/?text=${enc(`${title} — ${url}`)}`,
      icon: MessageCircle,
      cls: "bg-[#25D366] text-white hover:opacity-90",
    },
    {
      label: "Facebook",
      href: `https://www.facebook.com/sharer/sharer.php?u=${enc(url)}`,
      icon: Facebook,
      cls: "bg-[#1877F2] text-white hover:opacity-90",
    },
    {
      label: "Telegram",
      href: `https://t.me/share/url?url=${enc(url)}&text=${enc(title)}`,
      icon: Send,
      cls: "bg-[#26A5E4] text-white hover:opacity-90",
    },
  ];

  const nativeShare = async () => {
    if (typeof navigator !== "undefined" && navigator.share) {
      try {
        await navigator.share({ title, url });
      } catch {
        /* user cancelled */
      }
    } else {
      copy();
    }
  };

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(url);
      toast.success("Lien copié !");
    } catch {
      toast.error("Impossible de copier le lien");
    }
  };

  return (
    <div className="flex flex-wrap items-center gap-2">
      <span className="mr-1 inline-flex items-center gap-1.5 text-sm font-semibold text-foreground/80">
        <Share2 className="h-4 w-4" /> Partager :
      </span>
      {links.map((l) => (
        <a
          key={l.label}
          href={l.href}
          target="_blank"
          rel="noopener noreferrer"
          className={`inline-flex h-9 items-center gap-1.5 rounded-full px-3 text-xs font-semibold transition ${l.cls}`}
          aria-label={`Partager sur ${l.label}`}
        >
          <l.icon className="h-4 w-4" />
          {l.label}
        </a>
      ))}
      <button
        type="button"
        onClick={copy}
        className="inline-flex h-9 items-center gap-1.5 rounded-full border border-border bg-card px-3 text-xs font-semibold hover:border-accent"
      >
        <LinkIcon className="h-4 w-4" /> Copier
      </button>
      <button
        type="button"
        onClick={nativeShare}
        className="inline-flex h-9 items-center gap-1.5 rounded-full border border-border bg-card px-3 text-xs font-semibold hover:border-accent md:hidden"
      >
        <Share2 className="h-4 w-4" /> Plus
      </button>
    </div>
  );
}
