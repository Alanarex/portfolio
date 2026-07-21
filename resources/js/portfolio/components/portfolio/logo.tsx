import logoDark from "@/assets/logo-dark.png";
import logoLight from "@/assets/logo-light.png";
import { cn } from "@/lib/utils";

export function Logo({ className, variant = "auto" }: { className?: string; variant?: "auto" | "dark" | "light" }) {
  if (variant === "dark") return <img src={logoDark} alt="Logo Alaa Khalil" className={cn("block", className)} />;
  if (variant === "light") return <img src={logoLight} alt="Logo Alaa Khalil" className={cn("block", className)} />;
  return (
    <>
      <img src={logoLight} alt="Logo Alaa Khalil" className={cn("block dark:hidden", className)} />
      <img src={logoDark} alt="Logo Alaa Khalil" className={cn("hidden dark:block", className)} />
    </>
  );
}
