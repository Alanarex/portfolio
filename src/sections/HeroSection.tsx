import { Canvas } from "@react-three/fiber";
import { motion } from "framer-motion";
import "../styles/Hero.css";

const HeroSection = () => {
  return (
    <section className="h-screen w-full flex items-center justify-center relative overflow-hidden">
      <motion.h1
        className="text-[4.5rem] font-bold absolute w-full text-center tracking-wide"
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
          duration: 1.5,
          ease: "easeOut",
        }}
      >
        <span className="text-[4.5rem] block">Chef de projet Informatique</span>
        <span className="text-[4.5rem] block">Concepteur Développeur Fullstack</span>
        <span className="text-[2.5rem] font-extrabold mt-4 block">Alaa KHALIL</span>
      </motion.h1>
      <Canvas className="absolute z-[-1]">
        {/* <Laptop3D /> */}
      </Canvas>
    </section>
  );
};

export default HeroSection;
