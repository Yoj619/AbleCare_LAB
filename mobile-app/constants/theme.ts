export const Colors = {
  primary: '#3AAFA9',
  primaryDark: '#2B9E98',
  primaryLight: '#D4EFED',
  background: '#EAF6F5',
  white: '#FFFFFF',
  dark: '#1C3030',
  textPrimary: '#2C3E3E',
  textSecondary: '#5A7070',
  textMuted: '#9DB8B8',
  border: '#DCF0EE',
  inputBg: '#F5FAFA',
  orange: '#F0A500',
  orangeLight: '#FFF3D6',
  danger: '#D95F3B',
  dangerLight: '#FDE8E2',
  success: '#4CAF50',
  badgeRed: '#E05F3E',
  drawerBg: '#F0FAFA',
  callBg: '#2C2C2C',
  overlayBg: 'rgba(0,0,0,0.55)',
};

export const Typography = {
  size: {
    xs: 12,
    sm: 14,
    md: 16,
    lg: 20,
    xl: 24,
    xxl: 28,
  },
  weight: {
    regular: '400' as const,
    medium: '500' as const,
    semiBold: '600' as const,
    bold: '700' as const,
  },
};

export const Spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

export const Radius = {
  sm: 6,
  md: 10,
  lg: 16,
  xl: 24,
  full: 999,
};

export const Shadows = {
  card: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.07,
    shadowRadius: 8,
    elevation: 3,
  },
  header: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.12,
    shadowRadius: 6,
    elevation: 6,
  },
};
