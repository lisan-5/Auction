import AuctionCard from "@/components/AuctionCard";
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";
import { Filter } from "lucide-react";

const auctions = [
  {
    id: 1,
    title: "Sunset  Over Addis",
    description:
      "A vibrant depiction of Addis Ababa’s evening skyline, where warm orange and purple tones capture the transition from day to night. The piece reflects the bustling yet calm essence of the capital city as lights slowly illuminate the horizon.",
    type: "closed",
    status: "upcoming",
    thumbnail_url:
      "https://i.pinimg.com/1200x/cd/b4/3f/cdb43f9bc435ec94693a77d65d70fec8.jpg",
    start_time: new Date(Date.now() + 12 * 60 * 60 * 1000),
    end_time: new Date(Date.now() + 12 * 60 * 60 * 1000),
    starting_price: "10,000 ETB",
    min_increment: "500 ETB",
    current_price: "10,000 ETB",
    reserve_price: "12,000 ETB",
    ends_in_seconds: 259200,
    winner_bid_id: null,
    artist: {
      id: 101,
      name: "Abebe Tesfaye",
      email: "abebe@example.com",
    },
  },
  {
    id: 2,
    title: "The Silent Lake",
    description:
      "A calming view of Lake Tana at dawn, where soft blues and silvers reflect the still waters. This painting captures the peaceful silence of the lake as fishermen prepare their boats for the day ahead.",
    type: "closed",
    status: "live",
    thumbnail_url:
      "https://i.pinimg.com/736x/e7/08/01/e70801a14211a8e9552d2bd41deea869.jpg",
    start_time: new Date(Date.now() + 48 * 60 * 60 * 1000),
    end_time: new Date(Date.now() + 48 * 60 * 60 * 1000),
    starting_price: "5,500 ETB",
    min_increment: "250 ETB",
    current_price: "6,000 ETB",
    reserve_price: "7,000 ETB",
    ends_in_seconds: 7200,
    winner_bid_id: null,
    artist: {
      id: 102,
      name: "Alemayo Kumsa",
      email: "alemayo@example.com",
    },
  },
  {
    id: 3,
    title: "Market Day",
    description:
      "A colorful and dynamic scene from Merkato, Africa’s largest open market. Bright textiles, bustling traders, and fresh produce create a vivid sense of Ethiopia’s daily economic life.",
    type: "open",
    status: "live",
    thumbnail_url:
      "https://i.pinimg.com/736x/e7/08/01/e70801a14211a8e9552d2bd41deea869.jpg",
    start_time: new Date(Date.now() + 1 * 60 * 1000),
    end_time: new Date(Date.now() + 30 * 60 * 1000),
    starting_price: "8,000 ETB",
    min_increment: "300 ETB",
    current_price: "8,600 ETB",
    reserve_price: "9,000 ETB",
    ends_in_seconds: 5400,
    winner_bid_id: null,
    artist: {
      id: 103,
      name: "Mulugeta Bekele",
      email: "mulugeta@example.com",
    },
  },
  {
    id: 4,
    title: "Pastoral Harmony",
    description:
      "A touching depiction of Ethiopian highland shepherds guiding their flocks across rolling hills. The soft earth tones and pastoral theme emphasize the bond between people, animals, and land.",
    type: "closed",
    status: "ended",
    thumbnail_url:
      "https://i.pinimg.com/1200x/c9/3f/29/c93f2995d26b49f3a55c42f4fcdd13c4.jpg",
    start_time: new Date(Date.now() + 1 * 60 * 1000),
    end_time: new Date(Date.now() + 1 * 60 * 1000),
    starting_price: "15,000 ETB",
    min_increment: "600 ETB",
    current_price: "20,000 ETB",
    reserve_price: "18,000 ETB",
    ends_in_seconds: 3600,
    winner_bid_id: 201,
    artist: {
      id: 104,
      name: "Gurrmesa Jira",
      email: "gurrmesa@example.com",
    },
  },
  {
    id: 5,
    title: "Coffee Ceremony",
    description:
      "An intimate and detailed painting of Ethiopia’s traditional coffee ceremony. The artist captures the fragrance of roasted beans, the warmth of friendship, and the cultural ritual of hospitality.",
    type: "open",
    status: "ended",
    thumbnail_url:
      "https://i.pinimg.com/1200x/cd/b4/3f/cdb43f9bc435ec94693a77d65d70fec8.jpg",
    start_time: new Date(Date.now() + 2 * 60 * 1000),
    end_time: new Date(Date.now() + 2 * 60 * 1000),
    starting_price: "7,200 ETB",
    min_increment: "400 ETB",
    current_price: "25,500 ETB",
    reserve_price: "9,000 ETB",
    ends_in_seconds: 0,
    winner_bid_id: 202,
    artist: {
      id: 105,
      name: "Gagu Lati",
      email: "gagu@example.com",
    },
  },
  {
    id: 6,
    title: "Blue Nile Falls",
    description:
      "An energetic landscape of the famous Blue Nile Falls, also known as Tis Issat. The painting showcases rushing waters in bold strokes, with mist rising against a backdrop of lush greenery.",
    type: "closed",
    status: "upcoming",
    thumbnail_url:
      "https://i.pinimg.com/1200x/cd/b4/3f/cdb43f9bc435ec94693a77d65d70fec8.jpg",
    start_time: new Date(Date.now() + 11 * 60 * 1000),
    end_time: new Date(Date.now() + 11 * 60 * 1000),
    starting_price: "18,000 ETB",
    min_increment: "700 ETB",
    current_price: "18,000 ETB",
    reserve_price: "20,000 ETB",
    ends_in_seconds: 86400,
    winner_bid_id: null,
    artist: {
      id: 106,
      name: "Meskerem Haile",
      email: "meskerem@example.com",
    },
  },
];

function Auctions() {
  return (
    <div className="bg-gray-100">
      <section className="container mx-auto max-w-[86rem] px-6 py-8">
        <div className="mb-8">
          <h1 className="text-charcoal mb-2 text-4xl font-bold">
            Art Auctions
          </h1>
          <p className="text-muted-foreground">
            Discover and bid on exceptional artworks
          </p>
        </div>
        <div className="flex items-center justify-between gap-12">
          <input
            type="text"
            placeholder="Search by artwork title or artist..."
            className="border-charcoal/20 focus-visible:ring-ring placeholder:text-muted-foreground h-10 flex-1 rounded-md border px-4 placeholder:text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
          />
          <button className="border-charcoal/20 hover:bg-gold flex items-center gap-3 rounded-md border px-4 py-1.5 duration-200">
            <Filter size={15} />
            Filter
          </button>
        </div>
        <div className="border-primary/15 my-5 flex flex-wrap items-center justify-between rounded-md border bg-white">
          <div className="flex items-center gap-4 rounded-md px-4 py-2">
            <div className="border-border hover:bg-gold hover:text-gallery-white bg-secondary text-secondary-foreground cursor-pointer rounded-sm border px-4 py-1 text-sm duration-200">
              All
            </div>
            <div className="border-border hover:bg-gold hover:text-gallery-white bg-secondary text-secondary-foreground cursor-pointer rounded-sm border px-4 py-1 text-sm duration-200">
              Live
            </div>
            <div className="border-border hover:bg-gold hover:text-gallery-white bg-secondary text-secondary-foreground cursor-pointer rounded-sm border px-4 py-1 text-sm duration-200">
              Upcoming
            </div>
            <div className="border-border hover:bg-gold hover:text-gallery-white bg-secondary text-secondary-foreground cursor-pointer rounded-sm border px-4 py-1 text-sm duration-200">
              Finished
            </div>
          </div>
          <div className="flex items-center gap-4 rounded-md px-4 py-2">
            <div className="border-border hover:bg-gold hover:text-gallery-white bg-secondary text-secondary-foreground cursor-pointer rounded-sm border px-4 py-1 text-sm duration-200">
              Open Auction
            </div>
            <div className="border-border hover:bg-gold hover:text-gallery-white bg-secondary text-secondary-foreground cursor-pointer rounded-sm border px-4 py-1 text-sm duration-200">
              Closed Auction
            </div>
          </div>
        </div>

        <div className="mb-6 flex items-center justify-between">
          <p className="text-muted-foreground">
            Showing {auctions.length} of {auctions.length} auctions
          </p>
        </div>
        <div className="mx-auto mt-12 grid max-w-6xl gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {auctions.map((auction, idx) => (
            <AuctionCard key={idx} auction={auction} />
          ))}
        </div>

        <div className="text-muted-foreground mt-10 flex justify-center">
          <Pagination>
            <PaginationContent>
              <PaginationItem>
                <PaginationPrevious href="#" />
              </PaginationItem>

              <PaginationItem>
                <PaginationLink href="#">1</PaginationLink>
              </PaginationItem>

              <PaginationItem>
                <PaginationLink href="#">2</PaginationLink>
              </PaginationItem>

              <PaginationItem>
                <PaginationLink href="#" isActive>
                  3
                </PaginationLink>
              </PaginationItem>

              <PaginationItem>
                <PaginationLink href="#">4</PaginationLink>
              </PaginationItem>

              <PaginationItem>
                <PaginationEllipsis />
              </PaginationItem>

              <PaginationItem>
                <PaginationLink href="#">10</PaginationLink>
              </PaginationItem>

              <PaginationItem>
                <PaginationNext href="#" />
              </PaginationItem>
            </PaginationContent>
          </Pagination>
        </div>
      </section>
    </div>
  );
}

export default Auctions;
