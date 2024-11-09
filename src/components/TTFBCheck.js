import { useEffect } from '@wordpress/element';

const TTFBCheck = ({ isChecking, region, onComplete }) => {
  useEffect(() => {
    if (isChecking) {
      const startTTFBCheck = async () => {
        try {
          const formData = new FormData();
          formData.append('action', 'wpbb_ttfb_check');
          formData.append('security', wpbbSettings.nonce);
          formData.append('region', region);

          const response = await fetch(wpbbSettings.ajaxURL, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          });

          const data = await response.json();

          if (data.success) {
            console.log('TTFB data fetched successfully');
          } else {
            console.error('TTFBCheck error:', data.data);
          }
        } catch (err) {
          console.error('Error during TTFB check:', err);
        } finally {
          onComplete();
        }
      };
      startTTFBCheck();
    }
  }, [isChecking, region, onComplete]);

  return null;
};

export default TTFBCheck;