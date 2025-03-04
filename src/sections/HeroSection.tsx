import { Canvas } from "@react-three/fiber";
import { useScroll } from "framer-motion";
import Laptop3D from "../components/Laptop3D";

const HeroSection = () => {
  const { scrollYProgress } = useScroll();
  return (
    <section className="h-screen w-full flex items-center justify-center relative">
      <h1 className="text-6xl font-bold absolute top-1/3">Welcome to My Portfolio</h1>
      <Canvas className="absolute z-[-1]">
        <Laptop3D scrollYProgress={scrollYProgress} />
      </Canvas>
    </section>
  );
};
export default HeroSection;
