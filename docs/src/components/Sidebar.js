export class Sidebar {
    constructor() {
        this.sidebar = null;
        this.toggle = null;
        this.isOpen = false;
    }

    init() {
        this.sidebar = document.getElementById('sidebar');
        this.toggle = document.getElementById('sidebarToggle');

        if (!this.sidebar || !this.toggle) return;

        // Setup toggle
        this.toggle.addEventListener('click', () => this.toggleSidebar());

        // Close on outside click (mobile)
        if (window.innerWidth <= 768) {
            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.sidebar.contains(e.target) && !this.toggle.contains(e.target)) {
                    this.close();
                }
            });
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                this.sidebar.classList.remove('open');
                this.isOpen = false;
            }
        });
    }

    toggleSidebar() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        if (this.sidebar) {
            this.sidebar.classList.add('open');
            this.isOpen = true;
        }
    }

    close() {
        if (this.sidebar) {
            this.sidebar.classList.remove('open');
            this.isOpen = false;
        }
    }
}
