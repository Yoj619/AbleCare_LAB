import React from 'react';
import { Image, StyleSheet } from 'react-native';

type Size = 'sm' | 'md' | 'lg';

interface LogoProps {
  size?: Size;
}

const sizeMap: Record<Size, number> = { sm: 48, md: 72, lg: 100 };

export default function Logo({ size = 'md' }: LogoProps) {
  const dim = sizeMap[size];
  return (
    <Image
      source={require('../assets/ablecarelogo.png')}
      style={[styles.img, { width: dim, height: dim }]}
      resizeMode="contain"
    />
  );
}

const styles = StyleSheet.create({
  img: { alignSelf: 'center' },
});
