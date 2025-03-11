import { useFrame } from "@react-three/fiber";
import { useRef } from "react";
import { useGLTF, useTexture } from "@react-three/drei";
import * as THREE from "three";

type Laptop3DProps = {
  scrollYProgress: any;
};

const Laptop3D: React.FC<Laptop3DProps> = ({ scrollYProgress }) => {
  const laptopRef = useRef<THREE.Object3D | null>(null);
  const { scene, materials } = useGLTF("/model/laptop.glb");

  // Load textures
  const texture1 = useTexture("/textures/texture1.png");
  const texture2 = useTexture("/textures/texture2.png");

  // Ensure materials exist before applying textures
  if (materials) {
    if (materials["Material_Name_1"] instanceof THREE.MeshStandardMaterial) {
      materials["Material_Name_1"].map = texture1;
      materials["Material_Name_1"].needsUpdate = true;
    }

    if (materials["Material_Name_2"] instanceof THREE.MeshStandardMaterial) {
      materials["Material_Name_2"].map = texture2;
      materials["Material_Name_2"].needsUpdate = true;
    }
  }

  useFrame(() => {
    if (laptopRef.current) {
      laptopRef.current.rotation.y = scrollYProgress.get() * Math.PI * 2;
    }
  });

  return <primitive ref={laptopRef} object={scene} scale={1.5} />;
};

export default Laptop3D;
