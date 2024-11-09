import { useEffect } from '@wordpress/element';

const MediaOptimizationCheck = ({ isChecking, onComplete }) => {
  useEffect(() => {
    if (isChecking) {
      const startMediaOptimizationCheck = async () => {
        try {
          const formData = new FormData();
          formData.append('action', 'wpbb_media_optimization');
          formData.append('security', wpbbSettings.nonce);

          const response = await fetch(wpbbSettings.ajaxURL, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          });

          const data = await response.json();

          if (data.success) {
            console.log('Media optimization data collected');
          } else {
            console.error('MediaOptimizationCheck error:', data.data);
          }
        } catch (err) {
          console.error('Error during media optimization check:', err);
        } finally {
          onComplete();
        }
      };
      startMediaOptimizationCheck();
    }
  }, [isChecking, onComplete]);

  return null;
};

export default MediaOptimizationCheck;