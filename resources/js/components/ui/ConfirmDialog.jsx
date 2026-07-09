import { Modal } from './Modal';
import { Button } from './Button';
import { AlertTriangle } from 'lucide-react';

export function ConfirmDialog({ open, onClose, onConfirm, title = 'Confirm Delete', message, loading }) {
    return (
        <Modal open={open} onClose={onClose} title={title} size="sm">
            <div className="flex flex-col items-center text-center">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                    <AlertTriangle className="h-6 w-6 text-red-600" />
                </div>
                <p className="text-sm text-gray-600">{message}</p>
            </div>
            <div className="flex justify-end gap-3 mt-6">
                <Button variant="secondary" onClick={onClose} disabled={loading}>
                    Cancel
                </Button>
                <Button variant="danger" onClick={onConfirm} disabled={loading}>
                    {loading ? 'Deleting...' : 'Delete'}
                </Button>
            </div>
        </Modal>
    );
}
