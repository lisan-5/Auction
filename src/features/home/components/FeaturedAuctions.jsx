import AuctionCard from "@/components/AuctionCard";
import React, { useState, useEffect } from "react";
import { FaUsers } from "react-icons/fa";

const initial = [
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
    status: "live",
    thumbnail_url:
      "https://i.pinimg.com/1200x/cd/b4/3f/cdb43f9bc435ec94693a77d65d70fec8.jpg",
    start_time: new Date(Date.now() + 2 * 60 * 1000),
    end_time: new Date(Date.now() + 50 * 60 * 1000),
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
    start_time: new Date(Date.now() + 15 * 60 * 1000),
    end_time: new Date(Date.now() + 30 * 60 * 1000),
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

function Countdown({ endTime, onEnd }) {
  const [timeLeft, setTimeLeft] = useState("");
  const [isEndingSoon, setIsEndingSoon] = useState(false);

  useEffect(() => {
    if (!endTime) return;
    const interval = setInterval(() => {
      const now = new Date().getTime();
      const distance = endTime - now;

      if (distance <= 0) {
        clearInterval(interval);
        setTimeLeft("Ended");
        onEnd && onEnd(); // notify parent to update status
      } else {
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
          (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
        );
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        setTimeLeft(`${days}d ${hours}h ${minutes}m ${seconds}s`);
        setIsEndingSoon(distance < 30 * 60 * 1000); // less than 30 min
      }
    }, 1000);

    return () => clearInterval(interval);
  }, [endTime, onEnd]);

  return (
    <span className={isEndingSoon ? "font-semibold text-red-500" : ""}>
      {timeLeft || "Starting Soon"}
    </span>
  );
}

export default function FeaturedAuctions() {
  return (
    <div className="min-h-screen bg-gray-100 px-4 py-10">
      <div className="mx-auto max-w-6xl text-center">
        <h2 className="mt-4 mb-2 text-3xl font-bold">
          Featured <span className="text-yellow-500">Auctions</span>
        </h2>
        <p className="text-gray-600">
          Discover extraordinary artworks currently available for bidding. Each
          piece
        </p>
        <p className="mb-8 text-gray-600">
          has been carefully curated by our expert team.
        </p>

        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {initial.map((auction, index) => (
            <AuctionCard key={`${auction.id}${index}`} auction={auction} />
          ))}
        </div>

        {/* View All Button */}
        <button className="mt-8 rounded-lg bg-yellow-500 px-6 py-2 font-medium text-white hover:bg-yellow-600">
          View All Auctions
        </button>
      </div>
    </div>
  );
}
