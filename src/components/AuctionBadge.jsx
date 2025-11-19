import Badge from "./Badge";

function AuctionBadge({ type, status }) {
  return (
    <>
      <div className="absolute top-3 left-3">
        <Badge type={status} className="transition hover:opacity-90" />
      </div>

      <div className="group/badge absolute top-3 right-3">
        <Badge type={type} className="transition hover:opacity-90" />
        <span className="pointer-events-none absolute top-full right-0 z-50 mt-1 w-48 rounded bg-gray-800 p-2 text-xs text-white opacity-0 shadow-lg transition-opacity group-hover/badge:opacity-100">
          {type === "Open Auction"
            ? "Everyone can see the current bid amount and place their bids."
            : "Bid amounts are hidden. The winner of the auction is selected by the admins after when the auction ends."}
        </span>
      </div>
    </>
  );
}
export default AuctionBadge;
