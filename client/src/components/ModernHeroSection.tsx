import React, { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import { Link } from 'react-router-dom'
import { CheckCircle, Users, Award, Zap } from 'lucide-react'
import { ModernButton } from './ui/modern-button'
import { api, CompanyStats } from '@/lib/api'

const achievements = [
  {
    icon: Award,
    text: "Over 1000 successful projects delivered",
    metric: "1000+",
    label: "Projects"
  },
  {
    icon: Zap,
    text: "25+ years of engineering excellence", 
    metric: "25+",
    label: "Years"
  },
  {
    icon: Users,
    text: "500+ skilled professionals",
    metric: "500+", 
    label: "Professionals"
  },
  {
    icon: CheckCircle,
    text: "Trusted by 100+ leading clients",
    metric: "100+",
    label: "Clients"
  }
]

const ModernHeroSection: React.FC = () => {
  const [stats, setStats] = useState<CompanyStats | null>(null)

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await api.company.getStats()
        if (response.success) {
          setStats(response.data)
        }
      } catch (error) {
        console.error('Failed to fetch company stats:', error)
      }
    }

    fetchStats()
  }, [])

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        delayChildren: 0.3,
        staggerChildren: 0.2
      }
    }
  }

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: {
        duration: 0.5,
        ease: "easeOut"
      }
    }
  }

  const badgeVariants = {
    hidden: { scale: 0, opacity: 0 },
    visible: {
      scale: 1,
      opacity: 1,
      transition: {
        type: "spring",
        stiffness: 200,
        damping: 10,
        delay: 0.5
      }
    }
  }

  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden hero-background">
      {/* Animated Background Elements */}
      <div className="absolute inset-0">
        <motion.div
          animate={{ 
            rotate: 360,
            scale: [1, 1.1, 1]
          }}
          transition={{ 
            duration: 20,
            repeat: Infinity,
            ease: "linear"
          }}
          className="absolute top-1/4 left-1/4 w-32 h-32 bg-yellow-400/10 rounded-full blur-xl"
        />
        <motion.div
          animate={{ 
            rotate: -360,
            scale: [1, 1.2, 1]
          }}
          transition={{ 
            duration: 25,
            repeat: Infinity,
            ease: "linear"
          }}
          className="absolute bottom-1/4 right-1/4 w-40 h-40 bg-blue-400/10 rounded-full blur-xl"
        />
      </div>

      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="space-y-8"
        >
          {/* Premium Badge */}
          <motion.div
            variants={badgeVariants}
            className="inline-flex items-center space-x-2 bg-yellow-400/20 backdrop-blur-sm border border-yellow-400/30 rounded-full px-4 py-2"
          >
            <Zap className="w-4 h-4 text-yellow-400" />
            <span className="text-sm font-medium text-yellow-400">Premium Engineering Solutions</span>
          </motion.div>

          {/* Main Heading */}
          <motion.div variants={itemVariants} className="space-y-4">
            <h1 className="text-4xl sm:text-6xl lg:text-7xl font-bold leading-tight">
              <span className="block">Nolads Engineering</span>
            </h1>
            
            <div className="space-y-2">
              <h2 className="text-2xl sm:text-3xl lg:text-4xl font-light text-blue-100">
                A Pinnacle Of
              </h2>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold gradient-text">
                Engineering Excellence.
              </h2>
            </div>
          </motion.div>

          {/* Description */}
          <motion.p 
            variants={itemVariants}
            className="text-lg sm:text-xl lg:text-2xl text-blue-100 max-w-4xl mx-auto leading-relaxed"
          >
            Nestled in the heart of innovation, Nolads Engineering offers transformative 
            industrial solutions where cutting-edge technology meets uncompromising precision.
          </motion.p>

          {/* Achievements List */}
          <motion.div
            variants={itemVariants}
            className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-w-4xl mx-auto"
          >
            {achievements.map((achievement, index) => {
              const Icon = achievement.icon

              // Get display metric based on stats
              const displayMetric = stats ? (() => {
                switch (achievement.label) {
                  case 'Projects':
                    return `${stats.projects_completed}+`
                  case 'Years':
                    return `${stats.years_experience}+`
                  case 'Clients':
                    return `${stats.clients_served}+`
                  default:
                    return achievement.metric
                }
              })() : achievement.metric
              
              return (
                <motion.div
                  key={index}
                  variants={{
                    hidden: { opacity: 0, y: 20 },
                    visible: {
                      opacity: 1,
                      y: 0,
                      transition: {
                        delay: 0.8 + index * 0.1,
                        duration: 0.5
                      }
                    }
                  }}
                  className="flex items-center space-x-3 text-left"
                >
                  <div className="flex-shrink-0">
                    <div className="w-8 h-8 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                      <Icon className="w-4 h-4 text-yellow-400" />
                    </div>
                  </div>
                  <div className="text-sm text-blue-100">
                    {achievement.text.replace(achievement.metric, displayMetric)}
                  </div>
                </motion.div>
              )
            })}
          </motion.div>

          {/* Action Buttons */}
          <motion.div 
            variants={itemVariants}
            className="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6"
          >
            <ModernButton
              variant="gradient"
              size="xl"
              className="text-lg px-8 py-4"
              asChild
            >
              <Link to="/quote">Request Quote</Link>
            </ModernButton>
            
            <ModernButton
              variant="outline"
              size="xl"
              className="text-lg px-8 py-4 border-white/30 text-white hover:bg-white hover:text-blue-900"
              asChild
            >
              <Link to="/portfolio">View Our Work</Link>
            </ModernButton>
          </motion.div>
        </motion.div>
      </div>

      {/* Statistics Cards */}
      <motion.div
        initial={{ opacity: 0, y: 50 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 1.5, duration: 0.8 }}
        className="absolute bottom-8 left-1/2 transform -translate-x-1/2 w-full max-w-6xl mx-auto px-4"
      >
        <div className="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-6">
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
            {achievements.map((achievement, index) => {
              const Icon = achievement.icon

              // Get display metric based on stats
              const displayMetric = stats ? (() => {
                switch (achievement.label) {
                  case 'Projects':
                    return `${stats.projects_completed}+`
                  case 'Years':
                    return `${stats.years_experience}+`
                  case 'Clients':
                    return `${stats.clients_served}+`
                  default:
                    return achievement.metric
                }
              })() : achievement.metric
              
              return (
                <motion.div
                  key={index}
                  initial={{ scale: 0 }}
                  animate={{ scale: 1 }}
                  transition={{ 
                    delay: 1.8 + index * 0.1,
                    type: "spring",
                    stiffness: 200,
                    damping: 10
                  }}
                  className="text-center"
                >
                  <div className="w-12 h-12 bg-yellow-400/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <Icon className="w-6 h-6 text-yellow-400" />
                  </div>
                  <div className="text-2xl lg:text-3xl font-bold text-white mb-1">
                    {displayMetric}
                  </div>
                  <div className="text-sm text-blue-200">
                    {achievement.label}
                  </div>
                </motion.div>
              )
            })}
          </div>
        </div>
      </motion.div>

      {/* CTA Section */}
      <motion.div
        initial={{ opacity: 0, x: 50 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ delay: 2, duration: 0.8 }}
        className="absolute right-8 top-1/2 transform -translate-y-1/2 hidden xl:block"
      >
        <div className="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-6 max-w-sm">
          <h3 className="text-xl font-bold text-white mb-2">
            Transform your operations with
          </h3>
          <p className="text-blue-200 mb-4 text-sm">
            world-class engineering solutions
          </p>
          <ModernButton
            variant="gradient"
            size="lg"
            className="w-full"
            asChild
          >
            <Link to="/quote">Get Custom Quote</Link>
          </ModernButton>
        </div>
      </motion.div>
    </section>
  )
}

export default ModernHeroSection
