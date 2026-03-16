import { useEffect, useRef } from 'react';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

export default function MarkdownEditor({
    id,
    value,
    onChange,
    placeholder = '',
    className = '',
    rows = 6,
    textareaClassName = '',
    autoFocus = false,
}) {
    const currentValue = value ?? '';
    const editorHeightClass = rows >= 8 ? 'min-h-[220px]' : 'min-h-[160px]';
    const editorContentHeightClass =
        rows >= 8 ? '[&_.ql-editor]:min-h-[220px]' : '[&_.ql-editor]:min-h-[160px]';
    const quillRef = useRef(null);

    const modules = {
        toolbar: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
            ['blockquote', 'code-block'],
            ['link'],
            ['clean'],
        ],
    };

    const formats = [
        'header',
        'bold',
        'italic',
        'underline',
        'strike',
        'list',
        'bullet',
        'indent',
        'blockquote',
        'code-block',
        'link',
    ];

    useEffect(() => {
        if (!autoFocus) {
            return;
        }

        const frame = window.requestAnimationFrame(() => {
            quillRef.current?.focus?.();
        });

        return () => window.cancelAnimationFrame(frame);
    }, [autoFocus]);

    return (
        <div className={className}>
            <div
                className={`overflow-hidden rounded-2xl border border-slate-300 bg-white [&_.ql-toolbar]:rounded-none [&_.ql-toolbar]:border-0 [&_.ql-toolbar]:border-b [&_.ql-toolbar]:border-slate-200 [&_.ql-container]:rounded-none [&_.ql-container]:border-0 [&_.ql-editor]:rounded-none [&_.ql-editor]:text-sm ${editorContentHeightClass} ${textareaClassName}`}
            >
                <ReactQuill
                    ref={quillRef}
                    id={id}
                    theme="snow"
                    value={currentValue}
                    onChange={onChange}
                    placeholder={placeholder}
                    modules={modules}
                    formats={formats}
                    className={editorHeightClass}
                />
            </div>
        </div>
    );
}
