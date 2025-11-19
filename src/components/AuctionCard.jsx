import { Clock, Users } from "lucide-react";
import { cn } from "@/lib/utils";
import { Link } from "react-router";
import AuctionBadge from "./AuctionBadge";
import { useCountdown } from "@/hooks/useCountDown";

function AuctionCard({
  auction: {
    id,
    type,
    status,
    artist: { name },
    title,
    start_time,
    end_time,
    current_price = "",
    starting_price,
    numBiders,
    thumbnail_url: image,
  },
}) {
  const auction = {
    second_info: {
      live: {
        timeClass: "text-auction-green",
        timeValue: useCountdown(end_time),
        buttonClass: "cursor-pointer",
        buttonValue: "Place Bid",
      },
      upcoming: {
        timeClass: "text-blue-400",
        timeValue: useCountdown(start_time),
        buttonClass:
          "cursor-pointer bg-transparent hover:bg-gold/20 text-gold border border-gold",
        buttonValue: "View Details",
      },
      ended: {
        timeClass: "text-destructive",
        timeValue: "Ended",
        buttonClass:
          "cursor-pointer bg-transparent hover:bg-gold/20 text-gold border border-gold",
        buttonValue: "View Details",
      },
    },
    open: {
      first_info: {
        live: { label: "Current Bid", value: current_price },
        upcoming: { label: "Starting Amount", value: starting_price },
        ended: { label: "Winner Bid", value: current_price },
      },
    },
    closed: {
      first_info: {
        live: { label: "Starting Bid", value: starting_price },
        upcoming: { label: "Starting Bid", value: starting_price },
        ended: { label: "Winner Bid", value: "Sealed" },
      },
    },
  };

  const firstInfo = auction[type]?.first_info?.[status] ??
    auction[type]?.[status] ?? { label: "", value: "" };

  const secondInfo = auction.second_info?.[status] ?? {
    timeClass: "",
    timeValue: "",
    buttonClass: "",
    buttonValue: "View",
  };

  return (
    <Link to={`/auctions/${id}`}>
      <div className="border-border group text-card-foreground relative w-full max-w-[25rem] overflow-hidden rounded-lg border bg-white shadow-md transition-all duration-300 hover:shadow-lg">
        <div className="relative z-10 flex w-full items-center justify-between px-4">
          <AuctionBadge status={status} type={type} />
        </div>

        <div className="relative overflow-hidden">
          <img
            src={image}
            className="h-50 w-full object-cover transition-transform duration-500 group-hover:scale-105"
            alt={title}
          />
        </div>

        <div className="space-y-3 bg-white px-4 py-4">
          <div>
            <h3 className="group-hover:text-auction-green font-semibold transition-colors">
              {title}
            </h3>
            <p className="text-muted-foreground text-sm">by {name}</p>
          </div>

          <div className="flex items-center justify-between">
            <p className="text-muted-foreground text-sm">{firstInfo.label}</p>
            <p className="text-auction-green text-sm font-bold">
              {firstInfo.value}
            </p>
          </div>

          <div className="text-muted-foreground flex items-center justify-between">
            <p className="flex items-center gap-2 text-sm">
              <Users size={15} /> {numBiders} bids
            </p>
            <p
              className={cn(
                "flex items-center gap-2 text-sm",
                secondInfo.timeClass,
              )}
            >
              <Clock size={14} /> {secondInfo.timeValue}
            </p>
          </div>

          <button
            className={cn(
              "bg-auction-green text-gallery-white hover:bg-auction-green/90 w-full rounded-lg px-4 py-1.5 transition-all duration-300 hover:scale-[1.02]",
              secondInfo.buttonClass,
            )}
          >
            {secondInfo.buttonValue}
          </button>
        </div>
      </div>
    </Link>
  );
}

export default AuctionCard;
