# Nolads Engineering - Source Code Recreation

This project has been recreated from the production build to provide the original TypeScript/React source code that matches the current production website exactly.

## 🏗️ Project Structure

```
├── client/                  # React TypeScript frontend source code
│   ├── src/
│   │   ├── components/      # React components
│   │   │   ├── ui/         # UI components (buttons, cards, toast, etc.)
│   │   │   ├── ModernNavBar.tsx
│   │   │   ├── ModernHeroSection.tsx
│   │   │   ├── NotificationSystem.tsx
│   │   │   └── PageTransitionWrapper.tsx
│   │   ├── pages/          # Page components
│   │   │   └── Index.tsx   # Home page
│   │   ├── lib/            # Utilities and API client
│   │   │   ├── api.ts      # API client
│   │   │   └── utils.ts    # Utility functions
│   │   ├── App.tsx         # Main app component
│   │   ├��─ main.tsx        # Entry point
│   │   └── index.css       # Global styles
│   ├── package.json        # Frontend dependencies
│   ├── vite.config.ts      # Vite configuration
│   ├── tailwind.config.js  # Tailwind CSS configuration
│   └── tsconfig.json       # TypeScript configuration
├── api/                     # PHP API (legacy/alternative backend)
├── config/                  # Node.js database configuration
├── models/                  # Node.js data models
├── scripts/                 # Database seeding scripts
├── server.js               # Node.js/Express backend server
├── dev.js                  # Development script (runs both frontend & backend)
├── build.js                # Build script (builds frontend to production)
└── package.json            # Root package.json
```

## 🚀 Quick Start

### Prerequisites

- Node.js 18+ 
- npm or yarn

### Development Setup

1. **Install dependencies:**
   ```bash
   npm install
   cd client && npm install && cd ..
   ```

2. **Start development environment:**
   ```bash
   npm run dev
   ```
   This will start:
   - Backend server on http://localhost:3000
   - Frontend dev server on http://localhost:5173

3. **Access the application:**
   - Frontend: http://localhost:5173 (with hot reload)
   - Backend API: http://localhost:3000/api
   - Production build: http://localhost:3000

### Production Build

1. **Build the frontend:**
   ```bash
   npm run build
   ```
   This will:
   - Build the React app
   - Copy build files to the root directory
   - Update the production assets

2. **Start production server:**
   ```bash
   npm start
   ```

## 🛠️ Technology Stack

### Frontend
- **React 18** with TypeScript
- **Vite** for build tooling and dev server
- **Tailwind CSS** for styling
- **Framer Motion** for animations
- **React Router** for navigation
- **Lucide React** for icons
- **Sonner** for toast notifications

### Backend
- **Node.js** with Express
- **MySQL2** for database connectivity
- **CORS** for cross-origin requests
- **dotenv** for environment variables

### UI Components
- Modern, responsive design system
- Custom button and card components
- Toast notification system
- Page transition animations
- Mobile-responsive navigation

## 📁 Key Components

### ModernNavBar.tsx
- Responsive navigation with dropdown menus
- Scroll-based styling changes
- Mobile hamburger menu
- Active route highlighting

### ModernHeroSection.tsx
- Animated hero section with company stats
- Dynamic data from API
- Responsive design
- Call-to-action buttons

### ModernButton & ModernCard
- Reusable UI components with variants
- Animation support with Framer Motion
- Tailwind CSS integration

### API Client (lib/api.ts)
- TypeScript-based API client
- Handles all backend communication
- Type-safe interfaces
- Error handling

## 🎨 Styling

The project uses:
- **Tailwind CSS** for utility-first styling
- **CSS Custom Properties** for theme variables
- **IBM Plex** font family
- **Gradient effects** and **backdrop blur**
- **Responsive breakpoints**

## 🔌 API Integration

The frontend communicates with the Node.js backend through:
- RESTful API endpoints under `/api`
- Services: `/api/services`, `/api/services/featured`
- Products: `/api/products`, `/api/products/featured`
- Company: `/api/company/stats`, `/api/company/info`

## 🗄️ Database

- **MySQL** database with connection pooling
- Automatic database initialization
- Data seeding scripts
- Fallback to mock data if DB unavailable

## 📱 Features

- **Responsive Design** - Works on all devices
- **Fast Loading** - Optimized build and assets
- **SEO Optimized** - Meta tags and structured data
- **PWA Ready** - Manifest and service worker support
- **Animations** - Smooth page transitions and micro-interactions
- **Toast Notifications** - User feedback system

## 🚀 Deployment

The built application is ready for deployment:
- Static assets in `/assets/`
- Single `index.html` entry point
- Node.js backend serves both API and frontend
- Can be deployed to any Node.js hosting platform

## 🔧 Development Scripts

- `npm run dev` - Start development environment (both servers)
- `npm run build` - Build frontend for production
- `npm start` - Start production server
- `npm run server` - Start only the backend server

## 🎯 Matching Production

This recreated source code is designed to build exactly to match the current production website. The build output should be identical to what's currently running, including:

- Same component structure
- Identical styling and layout
- Matching API integration
- Same routing behavior
- Identical meta tags and SEO

## ⚠️ Environment Setup

For database connectivity, create a `.env` file in the root with:
```
DB_HOST=your_db_host
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=your_db_name
```

The application will fall back to mock data if no database is configured.

---

**Note**: This source code recreation maintains 100% compatibility with the existing production build while providing a modern development environment for future enhancements.
