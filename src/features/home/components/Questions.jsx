import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

export default function Question() {
  return (
    <section className="mx-auto my-10 mt-10 w-full max-w-3xl px-4">
      <h2 className="font-quicksand mb-8 text-center text-2xl font-bold text-teal-900 md:text-3xl">
        Frequently Asked Questions
      </h2>
      <Accordion
        type="single"
        collapsible
        className="w-full space-y-2"
        defaultValue="item-1"
      >
        <AccordionItem value="item-1" className="px-2">
          <AccordionTrigger className="py-4 text-lg font-medium">
            How does bidding work?
          </AccordionTrigger>
          <div className="w-full bg-teal-900" />
          <AccordionContent className="flex flex-col gap-4 py-4 text-gray-600">
            <p>
              Bidding is conducted in real time. You’ll see the current highest
              bid and can place your own. Once the auction timer ends, the
              highest bidder wins the artwork.
            </p>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="item-2" className="px-2">
          <AccordionTrigger className="py-4 text-lg font-medium">
            How do I know the artwork is authentic?
          </AccordionTrigger>
          <div className="w-full bg-teal-900" />
          <AccordionContent className="flex flex-col gap-4 py-4 text-gray-600">
            <p>
              Every artwork listed comes with an authentcated user and
              documentation provided by verified artists, galleries, or auction
              houses.
            </p>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="item-3" className="px-2">
          <AccordionTrigger className="py-4 text-lg font-medium">
            Can I sell my own artwork through the platform?
          </AccordionTrigger>
          <div className="w-full bg-teal-900" />
          <AccordionContent className="flex flex-col gap-4 py-4 text-gray-600">
            <p>
              Yes. Artists and collectors can apply to list their works. Once
              approved, our curatorial team will assist with pricing, listing,
              and promotion.
            </p>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="item-4" className="px-2">
          <AccordionTrigger className="py-4 text-lg font-medium">
            Can I return an artwork if I change my mind?
          </AccordionTrigger>
          <div className="w-full bg-teal-900" />
          <AccordionContent className="flex flex-col gap-4 py-4 text-gray-600">
            <p>
              Due to the nature of auctions, all sales are final. However, if
              the artwork arrives damaged or is not as described, you may be
              eligible for a refund or replacement.
            </p>
          </AccordionContent>
        </AccordionItem>
      </Accordion>
    </section>
  );
}
