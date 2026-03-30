import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import rehypeRaw from 'rehype-raw';
import rehypeSanitize, { defaultSchema } from 'rehype-sanitize';

const baseTextClass = 'text-inherit';

export default function MarkdownText({ content, className = '' }) {
    const text = content || '';
    const sanitizeSchema = {
        ...defaultSchema,
        tagNames: [...(defaultSchema.tagNames || []), 'u'],
    };

    return (
        <div className={className}>
            <ReactMarkdown
                remarkPlugins={[remarkGfm]}
                rehypePlugins={[rehypeRaw, [rehypeSanitize, sanitizeSchema]]}
                components={{
                    // Neutralize heading size changes.
                    h1: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    h2: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    h3: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    h4: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    h5: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    h6: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    p: ({ children }) => (
                        <p className={`my-2 ${baseTextClass}`}>{children}</p>
                    ),
                    u: ({ children }) => <u>{children}</u>,
                    a: ({ href, children }) => (
                        <a
                            href={href}
                            target="_blank"
                            rel="noreferrer"
                            className="text-indigo-600 underline hover:text-indigo-800"
                        >
                            {children}
                        </a>
                    ),
                    ul: ({ children }) => (
                        <ul className="my-2 list-disc pl-5">{children}</ul>
                    ),
                    ol: ({ children }) => (
                        <ol className="my-2 list-decimal pl-5">{children}</ol>
                    ),
                    blockquote: ({ children }) => (
                        <blockquote className="my-2 border-l-2 border-slate-300 pl-3 italic text-slate-600">
                            {children}
                        </blockquote>
                    ),
                    code: ({ inline, children }) =>
                        inline ? (
                            <code className="rounded bg-slate-100 px-1 py-0.5 text-[0.95em]">
                                {children}
                            </code>
                        ) : (
                            <code className="block overflow-x-auto rounded bg-slate-100 p-2 text-[0.9em]">
                                {children}
                            </code>
                        ),
                }}
            >
                {text}
            </ReactMarkdown>
        </div>
    );
}

