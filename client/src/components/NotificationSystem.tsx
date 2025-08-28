import React from 'react'
import { toast } from 'sonner'
import { CheckCircle, AlertCircle, XCircle, Info } from 'lucide-react'

export type NotificationType = 'success' | 'error' | 'warning' | 'info'

interface NotificationOptions {
  title?: string
  description?: string
  duration?: number
  action?: {
    label: string
    onClick: () => void
  }
}

class NotificationManager {
  private getIcon(type: NotificationType) {
    switch (type) {
      case 'success':
        return <CheckCircle className="h-4 w-4" />
      case 'error':
        return <XCircle className="h-4 w-4" />
      case 'warning':
        return <AlertCircle className="h-4 w-4" />
      case 'info':
        return <Info className="h-4 w-4" />
      default:
        return <Info className="h-4 w-4" />
    }
  }

  show(type: NotificationType, message: string, options: NotificationOptions = {}) {
    const { title, description, duration = 4000, action } = options
    
    const toastOptions = {
      duration,
      icon: this.getIcon(type),
      description: description || message,
      action: action ? {
        label: action.label,
        onClick: action.onClick
      } : undefined,
    }

    switch (type) {
      case 'success':
        return toast.success(title || message, toastOptions)
      case 'error':
        return toast.error(title || message, toastOptions)
      case 'warning':
        return toast.warning(title || message, toastOptions)
      case 'info':
        return toast.info(title || message, toastOptions)
      default:
        return toast(title || message, toastOptions)
    }
  }

  success(message: string, options?: NotificationOptions) {
    return this.show('success', message, options)
  }

  error(message: string, options?: NotificationOptions) {
    return this.show('error', message, options)
  }

  warning(message: string, options?: NotificationOptions) {
    return this.show('warning', message, options)
  }

  info(message: string, options?: NotificationOptions) {
    return this.show('info', message, options)
  }

  dismiss(toastId?: string | number) {
    toast.dismiss(toastId)
  }

  dismissAll() {
    toast.dismiss()
  }
}

export const notifications = new NotificationManager()

// React component for notification system
export const NotificationSystem: React.FC = () => {
  return null // Sonner handles the rendering
}

export default NotificationSystem
