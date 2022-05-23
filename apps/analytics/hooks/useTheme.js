import { useEffect } from 'react';
import useStore, { setTheme } from 'store/app';
import { getItem, setItem } from 'lib/web';
import { THEME_CONFIG } from 'lib/constants';

const selector = state => state.theme;

export default function useTheme(resolve = false) {
  const defaultTheme =
    typeof window !== 'undefined'
      ? window?.matchMedia('(prefers-color-scheme: dark)')?.matches
        ? 'dark'
        : 'light'
      : 'light';
  const theme = useStore(selector) || getItem(THEME_CONFIG) || defaultTheme;
  const resolvedTheme = theme === 'system' ? defaultTheme : theme;

  function saveTheme(value) {
    setItem(THEME_CONFIG, value);
    setTheme(value);
  }

  useEffect(() => {
    document.body.setAttribute('data-theme', resolvedTheme);
  }, [theme, defaultTheme]);

  return [resolve ? resolvedTheme : theme, saveTheme];
}
