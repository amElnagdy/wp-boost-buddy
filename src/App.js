import { useState, useEffect, useCallback } from '@wordpress/element';
import { Button } from 'antd';
import TTFBCheck from './components/TTFBCheck';
import MediaOptimizationCheck from './components/MediaOptimizationCheck';

const App = () => {
  const [isChecking, setIsChecking] = useState(false);
  const [recommendations, setRecommendations] = useState([]);
  const [testsCompleted, setTestsCompleted] = useState(0);
  const totalTests = 2;
  const [selectedRegion, setSelectedRegion] = useState('');

  const handleTestCompletion = useCallback(() => {
    setTestsCompleted((prev) => prev + 1);
  }, []);

  const startAllChecks = () => {
    setIsChecking(true);
    setTestsCompleted(0);
    setRecommendations([]);
  };

  const fetchRecommendations = async () => {
    try {
      const formData = new FormData();
      formData.append('action', 'wpbb_get_recommendations');
      formData.append('security', wpbbSettings.nonce);

      const response = await fetch(wpbbSettings.ajaxURL, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      });

      const data = await response.json();

      if (data.success && data.data.success) {
        setRecommendations(data.data.recommendations);
      } else {
        console.error('Could not fetch recommendations');
      }
    } catch (err) {
      console.error('Error fetching recommendations:', err);
    } finally {
      setIsChecking(false);
    }
  };

  useEffect(() => {
    if (isChecking && testsCompleted === totalTests) {
      fetchRecommendations();
    }
  }, [isChecking, testsCompleted]);

  return (
    <div>
      <div>
        <label>Select your audience's region:</label>
        <select
          value={selectedRegion}
          onChange={(e) => setSelectedRegion(e.target.value)}
        >
          <option value="">Select a region</option>
          <option value="america">America</option>
          <option value="asia">Asia</option>
          <option value="europe">Europe</option>
        </select>
      </div>
      <Button onClick={startAllChecks} disabled={isChecking || !selectedRegion}>
        {isChecking ? 'Running Checks...' : 'Start All Checks'}
      </Button>
      <TTFBCheck
        isChecking={isChecking}
        region={selectedRegion}
        onComplete={handleTestCompletion}
      />
      <MediaOptimizationCheck
        isChecking={isChecking}
        onComplete={handleTestCompletion}
      />
      {recommendations.length > 0 && (
        <div>
          <h2>Recommendations</h2>
          <ul>
            {recommendations.map((rec, index) => (
              <li key={index}>{rec}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
};

export default App;