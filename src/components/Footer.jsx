import {
  Facebook,
  Phone,
  Mail,
  Instagram,
  MapPin,
  Twitter,
} from "lucide-react";
import { Button } from "./ui/button";
import { Link } from "react-router";

function Footer() {
  return (
    <footer className="bg-charcoal text-gallery-white">
      <div className="container mx-auto px-4 py-16">
        <div className="grid gap-8 min-[30rem]:grid-cols-2 lg:grid-cols-4">
          {/* Brand */}
          <div className="lg:col-span-1">
            <div className="mb-4 text-3xl font-bold">
              Canvas<span className="text-gold">Bid</span>
            </div>
            <p className="text-gallery-white/80 mb-6">
              Premier art auction platform connecting collectors with
              exceptional artworks from around the world.
            </p>
            <div className="flex space-x-4">
              <Button
                variant="ghost"
                size="icon"
                className="text-gallery-white hover:text-gold"
              >
                <Facebook />
              </Button>
              <Button
                variant="ghost"
                size="icon"
                className="text-gallery-white hover:text-gold"
              >
                <Instagram className="h-5 w-5" />
              </Button>
              <Button
                variant="ghost"
                size="icon"
                className="text-gallery-white hover:text-gold"
              >
                <Twitter className="h-5 w-5" />
              </Button>
            </div>
          </div>

          <div>
            <h4 className="text-gold mb-4 text-lg font-semibold">
              Quick Links
            </h4>
            <ul className="space-y-3">
              <li>
                <Link
                  to="/auctions"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Browse Auctions
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Featured Auction
                </Link>
              </li>
              <li>
                <Link
                  to="/how-it-works"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  How It Works
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  About
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Authentication
                </Link>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="text-gold mb-4 text-lg font-semibold">Support</h4>
            <ul className="space-y-3">
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Help Center
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Terms of Service
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  Shipping & Returns
                </Link>
              </li>
              <li>
                <Link
                  to="#"
                  className="text-gallery-white/80 hover:text-gold transition-colors"
                >
                  FAQs
                </Link>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="text-gold mb-4 text-lg font-semibold">Contact Us</h4>
            <div className="space-y-4">
              <div className="flex items-start space-x-3">
                <MapPin className="text-gold mt-1 h-5 w-5" />
                <div className="text-gallery-white/80">
                  <div>123 Gallery Street</div>
                  <div>Art District, Addis Ababa 1000</div>
                </div>
              </div>
              <div className="flex items-center space-x-3">
                <Phone className="text-gold h-5 w-5" />
                <span className="text-gallery-white/80">+251 346 5654</span>
              </div>
              <div className="flex items-center space-x-3">
                <Mail className="text-gold h-5 w-5" />
                <span className="text-gallery-white/80">
                  info@canvasbid.com
                </span>
              </div>
            </div>
          </div>
        </div>

        <div className="border-gallery-white/20 mt-10 flex flex-col items-center justify-between border-t md:flex-row">
          <p className="text-gallery-white/60 mt-10">
            © 2025 CanvasBid. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}

export default Footer;
