import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function VoteButtons({ initialScore, userVote, routeName, routeParams, selfVote }) {
    const [score, setScore] = useState(initialScore);
    const [selected, setSelected] = useState(userVote);

    const handleVote = (value) => {
        if (selfVote) {
            alert("You cannot vote on your own post/thread.");
            return;
        }

        router.post(route(routeName, routeParams), { vote: value });

        // Optimistic update
        if (selected === value) {
            setScore(score - value); // toggle off
            setSelected(0);
        } else {
            setScore(score + value - selected); // remove old vote, add new
            setSelected(value);
        }
    };

    return (
        <div className="flex flex-col items-center">
            <button
                onClick={() => handleVote(1)}
                className={`text-green-600 hover:text-green-800 ${selected === 1 ? 'font-bold' : ''}`}
            >
                ▲
            </button>
            <span className="text-sm">{score}</span>
            <button
                onClick={() => handleVote(-1)}
                className={`text-red-600 hover:text-red-800 ${selected === -1 ? 'font-bold' : ''}`}
            >
                ▼
            </button>
        </div>
    );
}