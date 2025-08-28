import React, { useState, useEffect } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { Menu, X, ChevronDown } from 'lucide-react'
import { ModernButton } from './ui/modern-button'
import { cn } from '@/lib/utils'

interface NavigationItem {
  label: string
  href: string
  children?: NavigationItem[]
}

const navigationItems: NavigationItem[] = [
  {
    label: 'Services',
    href: '/services',
    children: [
      { label: 'Power Systems Design', href: '/services/power-systems' },
      { label: 'Safety & Compliance', href: '/services/safety' },
      { label: 'Industrial Automation', href: '/services/automation' },
      { label: 'Performance Monitoring', href: '/services/monitoring' },
    ]
  },
  {
    label: 'Products',
    href: '/products'
  },
  {
    label: 'Blog',
    href: '/blog'
  },
  {
    label: 'About Us',
    href: '/about'
  },
  {
    label: 'Contact Us',
    href: '/contact'
  }
]

const ModernNavBar: React.FC = () => {
  const [isScrolled, setIsScrolled] = useState(false)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const [activeDropdown, setActiveDropdown] = useState<string | null>(null)
  const location = useLocation()

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50)
    }

    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  useEffect(() => {
    setIsMobileMenuOpen(false)
    setActiveDropdown(null)
  }, [location])

  const isActivePath = (href: string) => {
    if (href === '/') {
      return location.pathname === '/'
    }
    return location.pathname.startsWith(href)
  }

  const handleDropdownToggle = (label: string) => {
    setActiveDropdown(activeDropdown === label ? null : label)
  }

  return (
    <>
      <motion.nav
        initial={{ y: -100 }}
        animate={{ y: 0 }}
        transition={{ duration: 0.5 }}
        className={cn(
          "fixed top-0 left-0 right-0 z-50 transition-all duration-300",
          isScrolled 
            ? "bg-blue-900/95 backdrop-blur-lg border-b border-blue-800/50 shadow-lg" 
            : "bg-blue-900/80 backdrop-blur-sm"
        )}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16 lg:h-20">
            {/* Logo */}
            <Link to="/" className="flex items-center space-x-2">
              <motion.div
                whileHover={{ scale: 1.05 }}
                transition={{ type: "spring", stiffness: 400, damping: 10 }}
              >
                <div className="flex items-center space-x-2">
                  <div className="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <span className="text-black font-bold text-lg">N</span>
                  </div>
                  <div className="hidden sm:block">
                    <span className="text-xl font-bold text-white">NOLADS</span>
                    <span className="text-yellow-400 text-xl font-bold">ENG</span>
                  </div>
                </div>
              </motion.div>
            </Link>

            {/* Desktop Navigation */}
            <div className="hidden lg:flex items-center space-x-8">
              {navigationItems.map((item) => (
                <div key={item.label} className="relative">
                  {item.children ? (
                    <div
                      className="relative"
                      onMouseEnter={() => setActiveDropdown(item.label)}
                      onMouseLeave={() => setActiveDropdown(null)}
                    >
                      <button
                        className={cn(
                          "flex items-center space-x-1 px-3 py-2 rounded-lg transition-all duration-200",
                          isActivePath(item.href)
                            ? "text-yellow-400 bg-blue-800/50"
                            : "text-white hover:text-yellow-400 hover:bg-blue-800/30"
                        )}
                      >
                        <span className="font-medium">{item.label}</span>
                        <ChevronDown className="w-4 h-4" />
                      </button>

                      <AnimatePresence>
                        {activeDropdown === item.label && (
                          <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -10 }}
                            transition={{ duration: 0.2 }}
                            className="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2"
                          >
                            {item.children.map((child) => (
                              <Link
                                key={child.href}
                                to={child.href}
                                className="block px-4 py-3 text-gray-800 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                              >
                                {child.label}
                              </Link>
                            ))}
                          </motion.div>
                        )}
                      </AnimatePresence>
                    </div>
                  ) : (
                    <Link
                      to={item.href}
                      className={cn(
                        "px-3 py-2 rounded-lg transition-all duration-200 font-medium",
                        isActivePath(item.href)
                          ? "text-yellow-400 bg-blue-800/50"
                          : "text-white hover:text-yellow-400 hover:bg-blue-800/30"
                      )}
                    >
                      {item.label}
                    </Link>
                  )}
                </div>
              ))}
            </div>

            {/* Action Buttons */}
            <div className="hidden lg:flex items-center space-x-4">
              <ModernButton
                variant="outline"
                size="sm"
                className="border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-black"
                asChild
              >
                <Link to="/admin">Admin Page</Link>
              </ModernButton>
              
              <ModernButton
                variant="gradient"
                size="sm"
                asChild
              >
                <Link to="/quote">Request Quote</Link>
              </ModernButton>
            </div>

            {/* Mobile Menu Button */}
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="lg:hidden p-2 rounded-lg text-white hover:bg-blue-800/30 transition-colors duration-200"
            >
              {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        <AnimatePresence>
          {isMobileMenuOpen && (
            <motion.div
              initial={{ opacity: 0, height: 0 }}
              animate={{ opacity: 1, height: "auto" }}
              exit={{ opacity: 0, height: 0 }}
              transition={{ duration: 0.3 }}
              className="lg:hidden bg-blue-900/95 backdrop-blur-lg border-t border-blue-800/50"
            >
              <div className="px-4 py-4 space-y-4">
                {navigationItems.map((item) => (
                  <div key={item.label}>
                    {item.children ? (
                      <div>
                        <button
                          onClick={() => handleDropdownToggle(item.label)}
                          className="flex items-center justify-between w-full text-left py-2 text-white hover:text-yellow-400 transition-colors duration-200"
                        >
                          <span className="font-medium">{item.label}</span>
                          <ChevronDown 
                            className={cn(
                              "w-4 h-4 transition-transform duration-200",
                              activeDropdown === item.label && "rotate-180"
                            )}
                          />
                        </button>
                        
                        <AnimatePresence>
                          {activeDropdown === item.label && (
                            <motion.div
                              initial={{ opacity: 0, height: 0 }}
                              animate={{ opacity: 1, height: "auto" }}
                              exit={{ opacity: 0, height: 0 }}
                              transition={{ duration: 0.2 }}
                              className="ml-4 mt-2 space-y-2"
                            >
                              {item.children.map((child) => (
                                <Link
                                  key={child.href}
                                  to={child.href}
                                  className="block py-2 text-blue-200 hover:text-yellow-400 transition-colors duration-200"
                                >
                                  {child.label}
                                </Link>
                              ))}
                            </motion.div>
                          )}
                        </AnimatePresence>
                      </div>
                    ) : (
                      <Link
                        to={item.href}
                        className={cn(
                          "block py-2 font-medium transition-colors duration-200",
                          isActivePath(item.href)
                            ? "text-yellow-400"
                            : "text-white hover:text-yellow-400"
                        )}
                      >
                        {item.label}
                      </Link>
                    )}
                  </div>
                ))}
                
                <div className="pt-4 border-t border-blue-800/50 space-y-3">
                  <ModernButton
                    variant="outline"
                    size="sm"
                    className="w-full border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-black"
                    asChild
                  >
                    <Link to="/admin">Admin Page</Link>
                  </ModernButton>
                  
                  <ModernButton
                    variant="gradient"
                    size="sm"
                    className="w-full"
                    asChild
                  >
                    <Link to="/quote">Request Quote</Link>
                  </ModernButton>
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </motion.nav>

      {/* Spacer to prevent content from going under fixed navbar */}
      <div className="h-16 lg:h-20" />
    </>
  )
}

export default ModernNavBar
