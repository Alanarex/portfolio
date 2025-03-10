import { Canvas } from "@react-three/fiber";
import { motion } from "framer-motion";
import Laptop3D from "../components/Laptop3D";
import { Particles } from "../components/magicui/particles";
import "../styles/Hero.css";

const HeroSection = () => {
  return (
    <section className="h-screen w-full flex items-center justify-center relative overflow-hidden">
      <Particles
        className="absolute inset-0 z-0"
        staticity={50}
        quantity={400}
        size={0.5}
        ease={50}
        color={"#ffffff"}
        vx={0.3}
        vy={-0.3}
        refresh={true}
      />

      <motion.h1
        className="text-[4.5rem] font-bold absolute w-full text-center tracking-wide z-[2]"
        style={{
          fontFamily: "'Big Shoulders Display', sans-serif",
          lineHeight: "1.1",
          background: "linear-gradient(to bottom right, #FFFFFF 5%, #959494 68%)",
          WebkitBackgroundClip: "text",
          WebkitTextFillColor: "transparent",
          maxWidth: "80vw",
          whiteSpace: "nowrap",
          transformOrigin: "top center",
        }}

        initial={{ y: "100vh", opacity: 0, scale: 2 }}
        animate={{ y: "-8vh", opacity: 1, scale: 1 }}
        transition={{
          duration: parseFloat(import.meta.env.VITE_HERO_ANIMATION_DURATION) || 3,
          ease: "easeOut",
        }}
      >
        <span className="text-[4.5rem] block">Concepteur Développeur Fullstack</span>
        <span className="text-[4.5rem] block">Chef de projet Informatique</span>
        <span className="text-[2.5rem] font-extrabold mt-4 block">Alaa KHALIL</span>
      </motion.h1>
      <Canvas gl={{ alpha: true }} className="absolute z-[1]">
        <Laptop3D />
      </Canvas>
    </section>
  );
};

export default HeroSection;
