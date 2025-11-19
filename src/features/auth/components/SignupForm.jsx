import React, { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { FaEye, FaEyeSlash, FaGoogle } from "react-icons/fa";

const signupSchema = z
  .object({
    name: z.string().min(2, "Name must be at least 2 characters"),
    email: z.string().email("Invalid email address"),
    password: z.string().min(6, "Password must be at least 6 characters"),
    confirmPassword: z
      .string()
      .min(6, "Confirm Password must be at least 6 characters"),
  })
  .refine((data) => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  });

export default function SignupForm() {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    resolver: zodResolver(signupSchema),
  });

  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const onSubmit = (data) => {
    console.log("Signup Form Submitted:", data);
  };

  const handleGoogleSignIn = () => {
    console.log("Google Sign-In clicked");
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <label className="block text-sm font-medium text-gray-700">Name</label>
        <input
          type="text"
          {...register("name")}
          placeholder="Name"
          className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-700 focus:outline-none"
        />
        {errors.name && (
          <p className="mt-1 text-sm text-red-500">{errors.name.message}</p>
        )}
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700">Email</label>
        <input
          type="email"
          {...register("email")}
          placeholder="Email"
          className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-700 focus:outline-none"
        />
        {errors.email && (
          <p className="mt-1 text-sm text-red-500">{errors.email.message}</p>
        )}
      </div>

      <div className="relative">
        <label className="block text-sm font-medium text-gray-700">
          Password
        </label>
        <input
          type={showPassword ? "text" : "password"}
          {...register("password")}
          placeholder="Password"
          className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 focus:ring-2 focus:ring-teal-700 focus:outline-none"
        />
        <button
          type="button"
          className="absolute top-1/2 right-3 mt-3 -translate-y-1/2 transform text-gray-500"
          onClick={() => setShowPassword(!showPassword)}
        >
          {showPassword ? <FaEye /> : <FaEyeSlash />}
        </button>
        {errors.password && (
          <p className="mt-1 text-sm text-red-500">{errors.password.message}</p>
        )}
      </div>

      <div className="relative">
        <label className="block text-sm font-medium text-gray-700">
          Confirm Password
        </label>
        <input
          type={showConfirmPassword ? "text" : "password"}
          {...register("confirmPassword")}
          placeholder="Confirm Password"
          className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 focus:ring-2 focus:ring-teal-700 focus:outline-none"
        />
        <button
          type="button"
          className="absolute top-1/2 right-3 mt-3 -translate-y-1/2 transform text-gray-500"
          onClick={() => setShowConfirmPassword(!showConfirmPassword)}
        >
          {showConfirmPassword ? <FaEye /> : <FaEyeSlash />}
        </button>
        {errors.confirmPassword && (
          <p className="mt-1 text-sm text-red-500">
            {errors.confirmPassword.message}
          </p>
        )}
      </div>

      <button
        type="submit"
        className="w-full rounded-lg bg-teal-900 py-2 text-white transition hover:bg-teal-800"
      >
        Sign Up
      </button>

      <button
        type="button"
        onClick={handleGoogleSignIn}
        className="mt-2 flex w-full items-center justify-center rounded-lg border border-gray-300 py-2 transition hover:bg-gray-100"
      >
        <FaGoogle className="mr-2" /> Continue with Google
      </button>
    </form>
  );
}
