import React from "react";
import { Star } from "lucide-react";
import { InfiniteMovingCards } from "@/components/ui/infinite-moving-cards";

export default function Testimonials() {
  const testimonials = [
    {
      quote:
        "Its credibility standards rival those of major institutions. It's refreshing to see technology enhance rather than compromise quality.",
      name: "Abebe B.",
      title: "Museum Manager",
    },
    {
      quote:
        "I've been collecting for 20 years, and this platform offers the most seamless experience I've encountered. The bidding process is intuitive and exciting.",
      name: "Tesfa E.",
      title: "Private Collector",
    },
    {
      quote:
        "This platform has transformed how I discover and acquire art. The authentication process gives me complete confidence in every purchase.",
      name: "Belete C.",
      title: "Art Collector",
    },
  ];

  return (
    <section className="bg-gray-50 py-16 dark:bg-zinc-900">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 className="font-quicksand text-center text-3xl font-bold tracking-tight text-teal-900">
          What Our Community Says
        </h2>
        <p className="mt-2 text-center text-xl text-gray-600">
          Trusted by collectors, artists, and galleries worldwide
        </p>

        <div className="mt-12">
          <InfiniteMovingCards
            items={testimonials.map((t) => ({
              ...t,
              quote: (
                <div>
                  <div className="mb-3 flex text-yellow-400">
                    {Array(5)
                      .fill(0)
                      .map((_, i) => (
                        <Star key={i} className="h-5 w-5 fill-yellow-400" />
                      ))}
                  </div>
                  <p className="text-base leading-relaxed text-gray-700 dark:text-gray-200">
                    “{t.quote}”
                  </p>
                  <div className="mt-6 flex items-center">
                    <div className="flex flex-col">
                      <span className="font-quicksand font-semibold text-gray-900 dark:text-white">
                        {t.name}
                      </span>
                      <span className="font-quicksand text-sm text-gray-500 dark:text-gray-400">
                        {t.title}
                      </span>
                    </div>
                  </div>
                </div>
              ),
            }))}
            direction="left"
            speed="normal"
            pauseOnHover={true}
            className="gap-4"
          />
        </div>
      </div>
    </section>
  );
}
