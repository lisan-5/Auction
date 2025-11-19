import { useParams } from "react-router";

function Auction() {
  const { auctionId } = useParams();
  return <div>Auction ID: {auctionId}</div>;
}

export default Auction;
