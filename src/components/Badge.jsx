import { cn } from "@/lib/utils";

const badgeType = {
  live: "bg-auction-green text-gallery-white",
  upcoming: "bg-blue-400 text-gallery-white",
  ended: "bg-destructive text-destructive-foreground",
  open: "bg-background text-foreground",
  closed: "bg-foreground text-background",
};

function Badge({ type = "open_auction" }) {
  return (
    <div
      className={cn(
        "z-10 inline-flex h-5 items-center rounded-full px-3 text-xs capitalize",
        badgeType[type],
      )}
    >
      {type}
    </div>
  );
}

export default Badge;
