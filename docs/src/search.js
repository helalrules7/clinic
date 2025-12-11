export class Search {
    constructor() {
        this.searchIndex = [];
        this.searchModal = null;
        this.searchInput = null;
        this.searchResults = null;
        this.isOpen = false;
    }

    async init() {
        this.searchModal = document.getElementById('searchModal');
        this.searchInput = document.getElementById('searchInput');
        this.searchResults = document.getElementById('searchResults');
        const searchToggle = document.getElementById('searchToggle');

        if (!this.searchModal || !this.searchInput || !this.searchResults) return;

        // Build search index
        await this.buildSearchIndex();

        // Setup event listeners
        searchToggle?.addEventListener('click', () => this.open());
        
        this.searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close();
            }
        });

        // Close on outside click
        this.searchModal.addEventListener('click', (e) => {
            if (e.target === this.searchModal) {
                this.close();
            }
        });
    }

    async buildSearchIndex() {
        // Load search data
        try {
            const searchData = await import('./data/search-index.js');
            this.searchIndex = searchData.default || searchData;
        } catch (error) {
            // Build index from DOM if data file doesn't exist
            this.buildIndexFromDOM();
        }
    }

    buildIndexFromDOM() {
        // Build index from navigation links
        this.searchIndex = [];
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            const title = link.textContent.trim();
            if (href && title) {
                this.searchIndex.push({
                    title: title,
                    path: href,
                    href: href,
                    content: title
                });
            }
        });
    }

    open() {
        if (!this.searchModal) return;
        
        this.isOpen = true;
        this.searchModal.classList.add('active');
        this.searchInput.focus();
        this.searchInput.value = '';
        this.searchResults.classList.remove('active');
        this.searchResults.innerHTML = '';
    }

    close() {
        if (!this.searchModal) return;
        
        this.isOpen = false;
        this.searchModal.classList.remove('active');
        this.searchInput.value = '';
        this.searchResults.classList.remove('active');
        this.searchResults.innerHTML = '';
    }

    handleSearch(query) {
        if (!query || query.length < 2) {
            this.searchResults.classList.remove('active');
            this.searchResults.innerHTML = '';
            return;
        }

        const results = this.search(query);
        this.displayResults(results);
    }

    search(query) {
        const lowerQuery = query.toLowerCase();
        const results = [];

        // Search in index
        this.searchIndex.forEach(item => {
            const score = this.calculateScore(item, lowerQuery);
            if (score > 0) {
                results.push({ ...item, score });
            }
        });

        // Sort by score
        results.sort((a, b) => b.score - a.score);

        return results.slice(0, 10); // Top 10 results
    }

    calculateScore(item, query) {
        let score = 0;
        const title = (item.title || '').toLowerCase();
        const content = (item.content || '').toLowerCase();
        const path = (item.path || '').toLowerCase();

        // Exact title match
        if (title === query) score += 100;
        // Title starts with query
        else if (title.startsWith(query)) score += 50;
        // Title contains query
        else if (title.includes(query)) score += 25;

        // Path match
        if (path.includes(query)) score += 10;

        // Content match
        if (content.includes(query)) score += 5;

        return score;
    }

    displayResults(results) {
        if (!this.searchResults) return;

        if (results.length === 0) {
            this.searchResults.innerHTML = `
                <div class="search-result-item">
                    <div class="search-result-title">لا توجد نتائج</div>
                </div>
            `;
            this.searchResults.classList.add('active');
            return;
        }

        const html = results.map(result => `
            <div class="search-result-item" data-href="${result.href}">
                <div class="search-result-title">${this.highlight(result.title, this.searchInput.value)}</div>
                <div class="search-result-path">${result.path}</div>
            </div>
        `).join('');

        this.searchResults.innerHTML = html;
        this.searchResults.classList.add('active');

        // Add click handlers
        this.searchResults.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                const href = item.getAttribute('data-href');
                if (href) {
                    window.location.hash = href;
                    this.close();
                }
            });
        });
    }

    highlight(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    addToIndex(item) {
        this.searchIndex.push(item);
    }

    clearIndex() {
        this.searchIndex = [];
    }
}
