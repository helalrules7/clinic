
import { X } from 'lucide-react';
import { useEffect } from 'react';

interface ImageModalProps {
    src: string | null;
    onClose: () => void;
}

export default function ImageModal({ src, onClose }: ImageModalProps) {
    useEffect(() => {
        const handleEsc = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        if (src) window.addEventListener('keydown', handleEsc);
        return () => window.removeEventListener('keydown', handleEsc);
    }, [src, onClose]);

    if (!src) return null;

    return (
        <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4 cursor-zoom-out animate-in fade-in duration-200"
            onClick={onClose}
        >
            <button
                onClick={onClose}
                className="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full"
            >
                <X size={28} />
            </button>
            <img
                src={src}
                alt="Full size view"
                className="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl cursor-zoom-out"
                onClick={(e) => {
                    e.stopPropagation();
                    onClose();
                }}
            />
        </div>
    );
}
