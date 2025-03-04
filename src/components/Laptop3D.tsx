import { useFrame } from "@react-three/fiber";
import { useRef } from "react";
import { useGLTF } from "@react-three/drei";
import * as THREE from "three";

type Laptop3DProps = {
  scrollYProgress: any;
};

const Laptop3D: React.FC<Laptop3DProps> = ({ scrollYProgress }) => {
  const laptopRef = useRef<THREE.Object3D | null>(null);
  const { scene } = useGLTF("/laptop-model.glb");

  useFrame(() => {
    if (laptopRef.current) {
      laptopRef.current.rotation.y = scrollYProgress.get() * Math.PI * 2;
    }
  });

  return <primitive ref={laptopRef} object={scene} scale={1.5} />;
};
export default Laptop3D;
