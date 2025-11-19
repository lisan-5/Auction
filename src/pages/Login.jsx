import LoginForm from "@/features/auth/components/LoginForm";

const imageUrls = [
  "https://i.pinimg.com/1200x/ca/93/7f/ca937f9aeee1660e4b9a345c031a542d.jpg",
  "https://i.pinimg.com/736x/a3/f5/e7/a3f5e740475fe9a8b45c93f337b3fea8.jpg",
  "https://i.pinimg.com/736x/76/6b/fa/766bfa2bc028471fa1ff02e23d3ea4be.jpg",
  "https://i.pinimg.com/1200x/ac/ff/c1/acffc1d3df0519307c2c874e662059ef.jpg",
  "https://i.pinimg.com/1200x/7f/08/89/7f0889be52f2e3f38a13c2e68a31b49f.jpg",
  "https://i.pinimg.com/736x/a3/c1/5f/a3c15f9a330a60635eb900f8485f970b.jpg",
];
export default function LoginPage() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-teal-900">
      <div className="flex h-[500px] w-[800px] overflow-hidden rounded-2xl bg-white shadow-lg">
        {/* Left Side - Login Form */}
        <div className="flex w-1/2 flex-col justify-center p-10">
          <h1 className="mb-6 text-2xl font-semibold text-teal-900">
            Welcome back
          </h1>
          <LoginForm />
          <p className="mt-4 text-sm text-gray-600">
            Don’t have an account?{" "}
            <a
              href="/signup"
              className="font-medium text-teal-700 hover:underline"
            >
              Sign Up
            </a>
          </p>
        </div>
        <div className="grid w-1/2 grid-cols-2 items-center justify-center gap-2 bg-gray-50 p-4">
          {imageUrls.map((url, i) => (
            <div key={i} className="h-32 overflow-hidden rounded-lg">
              <img
                src={url}
                alt={`Artwork ${i + 1}`}
                className="h-full w-full object-cover"
              />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
