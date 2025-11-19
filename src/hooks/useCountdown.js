import { useEffect, useState } from "react";

function calculateTimeLeft(targetTime) {
  const diff = new Date(targetTime).getTime() - Date.now();
  if (diff <= 0) return null;

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  if (days > 0) {
    return `${days} day${days > 1 ? "s" : ""}`;
  }

  const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
  const minutes = Math.floor((diff / (1000 * 60)) % 60);
  const seconds = Math.floor((diff / 1000) % 60);

  return (
    `${hours.toString().padStart(2, "0")}:` +
    `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`
  );
}

export function useCountdown(targetTime) {
  const [timeLeft, setTimeLeft] = useState(() => calculateTimeLeft(targetTime));

  useEffect(() => {
    if (!targetTime) return;

    const interval = setInterval(() => {
      const newTime = calculateTimeLeft(targetTime);
      if (!newTime) {
        clearInterval(interval);
        setTimeLeft("00:00:00");
      } else {
        setTimeLeft(newTime);
      }
    }, 1000);

    return () => clearInterval(interval);
  }, [targetTime]);

  return timeLeft;
}
