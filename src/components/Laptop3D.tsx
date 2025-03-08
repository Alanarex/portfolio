import { useRef } from "react";
import { useGLTF } from "@react-three/drei";
import { useFrame } from "@react-three/fiber";
import * as THREE from "three";

const Laptop3D = () => {
  const laptopRef = useRef<THREE.Object3D | null>(null);
  const { scene } = useGLTF("/model/macbook_pro_2021.glb");
  // const { scene, materials } = useGLTF("/model/macbook_pro_2021.glb");

  // // Load textures
  // const texture1 = useTexture("/textures/texture1.png");
  // const texture2 = useTexture("/textures/texture2.png");

  // if (materials) {
  //   if (materials["Material_Name_1"] instanceof THREE.MeshStandardMaterial) {
  //     materials["Material_Name_1"].map = texture1;
  //     materials["Material_Name_1"].needsUpdate = true;
  //   }
  //   if (materials["Material_Name_2"] instanceof THREE.MeshStandardMaterial) {
  //     materials["Material_Name_2"].map = texture2;
  //     materials["Material_Name_2"].needsUpdate = true;
  //   }
  // }

  // Animation state
  let progress = 0;
  const animationDuration = parseFloat(import.meta.env.VITE_HERO_ANIMATION_DURATION) || 3; // seconds

  // Rotation and scale variables for easier customization
  const fullRotation = Math.PI * 2; // 360 degrees
  const finalYRotation = fullRotation + Math.PI / 4; // Full turn + 45 degrees
  const finalXRotation = Math.PI / 8; // Slight forward tilt
  const initialScale = 0.01;
  const finalScale = 15;
  const finalXPosition=0;
  const finalYPosition=0;
  const finalZPosition=-3;
  
  useFrame((_, delta) => {
    if (laptopRef.current && progress < 1) {
      progress += delta / animationDuration;
      progress = Math.min(progress, 1);
      const easedProgress = THREE.MathUtils.smoothstep(progress, 0, 1);

      // Interpolating properties smoothly
      laptopRef.current.position.lerp(new THREE.Vector3(finalXPosition, finalYPosition, finalZPosition), easedProgress);
      laptopRef.current.rotation.y = THREE.MathUtils.lerp(0, finalYRotation, easedProgress);
      laptopRef.current.rotation.x = THREE.MathUtils.lerp(0, finalXRotation, easedProgress);
      laptopRef.current.scale.setScalar(THREE.MathUtils.lerp(initialScale, finalScale, easedProgress));
      laptopRef.current.visible = easedProgress > 0.01; // Avoid flicker at start
    }
  });

  return <primitive ref={laptopRef} object={scene} visible={false} scale={[0.01, 0.01, 0.01]} />;
};

export default Laptop3D;
