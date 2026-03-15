import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function VoteButtons({ initialScore, userVote, routeName, routeParams, selfVote }) {
    const [score, setScore] = useState(Number(initialScore) || 0);
    const [selected, setSelected] = useState(Number(userVote) || 0);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleVote = (value) => {
        if (selfVote) {
            alert("You cannot vote on your own post/thread.");
            return;
        }
        if (isSubmitting) {
            return;
        }

        setIsSubmitting(true);
        const previousSelected = selected;
        const nextSelected = previousSelected === value ? 0 : value;

        setSelected(nextSelected);
        setScore((prevScore) => prevScore - previousSelected + nextSelected);

        router.post(
            route(routeName, routeParams),
            { vote: value },
            {
                preserveScroll: true,
                onError: () => {
                    setSelected(previousSelected);
                    setScore((prevScore) => prevScore + previousSelected - nextSelected);
                },
                onFinish: () => setIsSubmitting(false),
            },
        );
    };

    return (
        <div className="flex flex-col items-center">
            <button
                onClick={() => handleVote(1)}
                disabled={isSubmitting}
                className={`rounded px-1 transition ${
                    selected === 1
                        ? 'bg-green-100 text-green-700 font-bold dark:bg-green-900/40 dark:text-green-300'
                        : 'text-gray-400 hover:text-green-700 dark:text-gray-500 dark:hover:text-green-300'
                } ${isSubmitting ? 'cursor-not-allowed opacity-60' : ''}`}
            >
                ▲
            </button>
            <span className="text-sm">{score}</span>
            <button
                onClick={() => handleVote(-1)}
                disabled={isSubmitting}
                className={`rounded px-1 transition ${
                    selected === -1
                        ? 'bg-red-100 text-red-700 font-bold dark:bg-red-900/40 dark:text-red-300'
                        : 'text-gray-400 hover:text-red-700 dark:text-gray-500 dark:hover:text-red-300'
                } ${isSubmitting ? 'cursor-not-allowed opacity-60' : ''}`}
            >
                ▼
            </button>
        </div>
    );
}