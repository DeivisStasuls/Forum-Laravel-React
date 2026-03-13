import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function VoteButtons({ initialScore, userVote, routeName, routeParams, disabled }) {
    const [score, setScore] = useState(initialScore);
    const [currentVote, setCurrentVote] = useState(userVote); // 1, -1, or 0

    const handleVote = (value) => {
        if (disabled) {
            alert("You cannot vote on your own thread.");
            return;
        }

        const newVote = currentVote === value ? 0 : value;

        setScore(score - currentVote + newVote);
        setCurrentVote(newVote);

        router.post(route(routeName, routeParams), { vote: newVote });
    };

    return (
        <div className="flex flex-col items-center">
            <button
                onClick={() => handleVote(1)}
                disabled={disabled}
                className={`text-gray-400 transition-colors duration-150 ${
                    !disabled && 'hover:text-green-500'
                } ${currentVote === 1 ? 'text-green-600 font-bold' : ''} ${
                    disabled ? 'opacity-50 cursor-not-allowed' : ''
                }`}
            >
                ▲
            </button>
            <span className="text-sm">{score}</span>
            <button
                onClick={() => handleVote(-1)}
                disabled={disabled}
                className={`text-gray-400 transition-colors duration-150 ${
                    !disabled && 'hover:text-red-500'
                } ${currentVote === -1 ? 'text-red-600 font-bold' : ''} ${
                    disabled ? 'opacity-50 cursor-not-allowed' : ''
                }`}
            >
                ▼
            </button>
        </div>
    );
}