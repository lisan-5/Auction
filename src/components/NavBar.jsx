import { Menu, X } from "lucide-react";
import { Link, NavLink } from "react-router";

const Links = ["Home", "Auctions", "About", "Contact"];
function NavBar() {
  const activeClass = "text-gold font-semibold";
  const normalClass = "hover:text-gold";

  return (
    <nav className="bg-secondary border-border shadow-card sticky top-0 z-50 border-b py-3 backdrop-blur-sm">
      <div className="container mx-auto flex items-center justify-between px-4 xl:max-w-[88rem]">
        <div className="text-3xl font-bold">
          Canvas<span className="text-gold">Bid</span>
        </div>

        <ul className="hidden min-[50rem]:flex min-[50rem]:gap-6">
          {Links.map((link) => (
            <li key={link}>
              <NavLink
                to={`/${link.toLowerCase()}`}
                className={({ isActive }) =>
                  `${isActive ? activeClass : normalClass} rounded-md px-3 py-2`
                }
              >
                {link}
              </NavLink>
            </li>
          ))}
        </ul>

        <div className="flex items-center gap-4">
          <button className="bg-muted border-border rounded-md border px-3 py-[5px]">
            <Link to="/login">Login</Link>
          </button>
          <button className="bg-gradient-gold rounded-sm px-3 py-[5px]">
            <Link to="/signup">SignUp</Link>
          </button>

          <div className="relative h-9 w-10 min-[50rem]:hidden">
            <details className="absolute inset-y-0 rounded-md">
              <summary className="hover:bg-gold border-border flex h-9 w-10 cursor-pointer list-none items-center justify-center rounded-md border p-2 duration-200">
                <span className="sr-only">Open main menu</span>
                <Menu className="h-6 w-6 open:hidden" strokeWidth={1.25} />
                <X className="hidden h-6 w-6 open:block" strokeWidth={1.25} />
              </summary>

              <div className="bg-secondary fixed top-15 right-0 w-full border-t p-3">
                <nav className="flex w-full flex-col gap-2">
                  {Links.map((link) => (
                    <NavLink
                      key={link}
                      to={`/${link.toLowerCase()}`}
                      className={({ isActive }) =>
                        `${isActive ? activeClass : normalClass} py-1`
                      }
                      onClick={(e) => {
                        const details = e.currentTarget.closest("details");
                        if (details) details.open = false;
                      }}
                    >
                      {link}
                    </NavLink>
                  ))}
                </nav>
              </div>
            </details>
          </div>
        </div>
      </div>
    </nav>
  );
}

export default NavBar;
