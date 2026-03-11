import { useState } from 'react';
import axios from 'axios';

export default function VoteButtons({ routeName, routeParams, initialScore, userVote }) {
    const [score, setScore] = useState(Number(initialScore) || 0);
    const [vote, setVote] = useState(Number(userVote) || 0);

    const handleVote = async (value) => {
        try {
            await axios.post(route(routeName, routeParams), { value });

            // Update local score
            setScore(prev => prev + value - vote);
            setVote(value);
        } catch (e) {
            console.error(e);
        }
    };

    return (
        <div className="flex flex-col items-center">
            <button onClick={() => handleVote(1)}>▲</button>
            <span>{isNaN(score) ? 0 : score}</span>
            <button onClick={() => handleVote(-1)}>▼</button>
        </div>
    );
}