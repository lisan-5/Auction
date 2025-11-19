import Footer from "@/components/Footer";
import NavBar from "@/components/NavBar";
import AboutUs from "@/features/home/components/AboutUs";
import FeaturedAuctions from "@/features/home/components/FeaturedAuctions";
import Hero from "@/features/home/components/Hero";
import HowItWorks from "@/features/home/components/HowItWorks";
import Partners from "@/features/home/components/Partners";
import Questions from "@/features/home/components/Questions";
import Testimonials from "@/features/home/components/Testimonials";

function Home() {
  return (
    <div className="">
      <Hero />
      <FeaturedAuctions />
      <Partners />
      <HowItWorks />
      <AboutUs />
      <Testimonials />
      <Questions />
    </div>
  );
}

export default Home;
