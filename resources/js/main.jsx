import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import App from './app';

const el = document.getElementById('app');
if (el) {
    createRoot(el).render(<App />);
}
