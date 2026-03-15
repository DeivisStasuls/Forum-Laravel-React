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
}) {
    const currentValue = value ?? '';
    const editorHeightClass = rows >= 8 ? 'min-h-[220px]' : 'min-h-[160px]';

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

    return (
        <div className={className}>
            <div className="mb-2 flex items-center justify-end">
                <p className="text-xs text-slate-500">Visual editor</p>
            </div>
            <div className={`rounded-lg border border-slate-300 bg-white ${textareaClassName}`}>
                <ReactQuill
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
