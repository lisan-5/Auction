import { Button } from "@/components/ui/button";
import { ArrowRight, Gavel } from "lucide-react";

function Hero() {
  return (
    <section className="relative z-10 min-h-[91dvh] py-10">
      <div className="absolute inset-0">
        <img
          src="/hero.jpg"
          alt="Elegant art gallery interior"
          className="h-full w-full object-cover"
        />

        <div className="from-charcoal/80 via-charcoal/50 absolute inset-0 bg-gradient-to-r to-transparent" />
      </div>

      <div className="relative z-20 container mx-auto px-6 xl:max-w-[88rem]">
        <div className="text-gallery-white max-w-2xl">
          <h1 className="mb-6 text-5xl leading-tight font-bold md:text-7xl">
            Discover
            <div className="flex flex-col flex-wrap sm:flex-row md:flex-col">
              <span className="text-gold block mr-3">Exceptional </span>
              <span className="block">Art</span>
            </div>
          </h1>

          <p className="text-gallery-white/90 mb-8 text-lg leading-relaxed md:text-xl lg:text-2xl">
            Experience the thrill of art auctions. Bid on masterpieces from
            renowned artists and emerging talents in our premium auction
            platform.
          </p>

          <div className="flex flex-col gap-4 sm:flex-row">
            <Button
              variant="premium"
              size="lg"
              className="hover:drop-shadow-gold hover:text-gallery-white px-8 py-6 text-lg transition-transform duration-300 hover:-translate-y-1 hover:drop-shadow-xl/60"
            >
              <Gavel className="mr-2 h-5 w-5" />
              Explore Auctions
              <ArrowRight className="ml-2 h-5 w-5" />
            </Button>

            <Button
              variant="bid"
              size="lg"
              className="text-gallery-white border-gallery-white hover:bg-gallery-white hover:text-charcoal px-8 py-6 text-lg transition-all duration-300 hover:-translate-y-1"
            >
              Learn More
            </Button>
          </div>

          <div className="border-gallery-white/20 mt-12 flex items-center space-x-8 border-t pt-8">
            <div>
              <div className="text-gold text-3xl font-bold">2,500+</div>
              <div className="text-gallery-white/80">Artworks Sold</div>
            </div>
            <div>
              <div className="text-gold text-3xl font-bold">150+</div>
              <div className="text-gallery-white/80">Active Artists</div>
            </div>
            <div>
              <div className="text-gold text-3xl font-bold">$12M+</div>
              <div className="text-gallery-white/80">Total Value</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

export default Hero;
