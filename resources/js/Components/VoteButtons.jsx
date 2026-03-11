import { router } from '@inertiajs/react';

export default function VoteButtons({ votableType, votableId, votes, userVote }) {
    const vote = (value) => {
        router.post(route('votes.store'), {
            votable_type: votableType,
            votable_id: votableId,
            value: value,
        }, {
            preserveScroll: true,
        });
    };

    return (
        <div className="flex flex-col items-center text-sm">
            <button
                onClick={() => vote(1)}
                className={`text-lg ${
                    userVote === 1
                        ? 'text-orange-500'
                        : 'text-gray-400 hover:text-orange-500'
                }`}
            >
                ▲
            </button>

            <span className="font-semibold">{votes}</span>

            <button
                onClick={() => vote(-1)}
                className={`text-lg ${
                    userVote === -1
                        ? 'text-blue-500'
                        : 'text-gray-400 hover:text-blue-500'
                }`}
            >
                ▼
            </button>
        </div>
    );
}