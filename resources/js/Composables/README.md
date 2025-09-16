# Idle Monitoring Composable

A professional, event-driven composable for managing idle monitoring state across your Vue.js application.

## Features

- 🎯 **Centralized State Management** - Single source of truth for idle monitoring
- 🔄 **Event-Driven Architecture** - Subscribe to state changes and user interactions
- 🛡️ **Type Safety** - Full JSDoc type definitions for better developer experience
- 🚀 **Performance Optimized** - Efficient event handling and memory management
- 🔧 **Highly Configurable** - Flexible settings and customization options
- 📱 **Responsive Design** - Works seamlessly across all device sizes

## Quick Start

```javascript
import { useGlobalIdleMonitoring } from '@/Composables/useIdleMonitoring'

export default {
  setup() {
    const {
      isIdleMonitoringActive,
      currentWarningCount,
      isWarningModalVisible,
      startIdleMonitoring,
      stopIdleMonitoring,
      acknowledgeWarning,
      forceLogout,
      on: onIdleEvent
    } = useGlobalIdleMonitoring()

    // Start monitoring
    onMounted(() => {
      startIdleMonitoring(userId, settings, true)
    })

    // Listen to events
    onIdleEvent('idle:timeout', (data) => {
      console.log(`Warning ${data.warningCount}/${data.maxWarnings}`)
    })

    return {
      isIdleMonitoringActive,
      currentWarningCount,
      isWarningModalVisible,
      acknowledgeWarning,
      forceLogout
    }
  }
}
```

## API Reference

### State Properties

| Property | Type | Description |
|----------|------|-------------|
| `isIdleMonitoringActive` | `ComputedRef<boolean>` | Whether monitoring is currently active |
| `isWarningModalVisible` | `ComputedRef<boolean>` | Whether warning modal is visible |
| `currentWarningCount` | `ComputedRef<number>` | Current warning count (0-3) |
| `currentCountdown` | `ComputedRef<number>` | Current countdown value (0-10) |
| `currentUser` | `ComputedRef<number\|null>` | ID of user being monitored |
| `currentSettings` | `ComputedRef<IdleSettings\|null>` | Current monitoring settings |
| `maxWarnings` | `ComputedRef<number>` | Maximum warnings before logout (3) |

### Methods

#### `startIdleMonitoring(userId, settings, isEnabled)`
Start idle monitoring for a specific user.

**Parameters:**
- `userId` (number): The user ID to monitor
- `settings` (IdleSettings): Monitoring configuration
- `isEnabled` (boolean, optional): Whether monitoring is enabled (default: true)

**Events Emitted:**
- `monitoring:started` - When monitoring starts successfully
- `monitoring:disabled` - When monitoring is disabled
- `monitoring:already-running` - When monitoring is already running
- `monitoring:error` - When an error occurs

#### `stopIdleMonitoring()`
Stop idle monitoring and clean up resources.

**Events Emitted:**
- `monitoring:stopped` - When monitoring stops successfully
- `monitoring:error` - When an error occurs during cleanup

#### `acknowledgeWarning()`
Acknowledge the current warning and reset monitoring.

**Events Emitted:**
- `warning:acknowledged` - When user acknowledges the warning

#### `forceLogout()`
Force logout the current user.

**Events Emitted:**
- `logout:forced` - When force logout is initiated
- `logout:error` - When logout fails

#### `updateSettings(newSettings)`
Update monitoring settings for the current session.

**Parameters:**
- `newSettings` (Partial<IdleSettings>): New settings to merge

**Events Emitted:**
- `settings:updated` - When settings are updated

#### `getStatus()`
Get current monitoring status.

**Returns:** `IdleStatus` - Current monitoring state

### Event System

The composable provides a comprehensive event system for tracking state changes:

#### Core Events
- `monitoring:started` - Monitoring started
- `monitoring:stopped` - Monitoring stopped
- `monitoring:disabled` - Monitoring disabled
- `monitoring:error` - Error occurred

#### Warning Events
- `idle:timeout` - Idle timeout detected
- `warning:shown` - Warning modal displayed
- `warning:acknowledged` - User acknowledged warning
- `warning:reset` - Warning count reset
- `warning:timeout` - Warning countdown expired

#### API Events
- `api:warning-success` - API call successful
- `api:warning-error` - API call failed

#### Logout Events
- `logout:forced` - Force logout initiated
- `logout:error` - Logout failed

#### Timer Events
- `timer:reset` - Idle timer reset
- `countdown:tick` - Countdown timer tick

### Type Definitions

```typescript
interface IdleSettings {
  idle_timeout: number;           // Timeout in seconds
  idle_monitoring_enabled: boolean; // Whether monitoring is enabled
  max_idle_warnings: number;      // Maximum warnings before logout
}

interface IdleStatus {
  isRunning: boolean;             // Whether monitoring is active
  currentUserId: number | null;   // ID of user being monitored
  warningCount: number;           // Current warning count
  showWarningModal: boolean;      // Whether warning modal is visible
  countdown: number;              // Current countdown value
  settings: IdleSettings | null;  // Current settings
}
```

## Usage Examples

### Basic Component Usage

```vue
<template>
  <div>
    <p v-if="isIdleMonitoringActive">
      Monitoring active for user {{ currentUser }}
    </p>
    <p v-if="isWarningModalVisible">
      Warning {{ currentWarningCount }}/3 - {{ currentCountdown }}s remaining
    </p>
  </div>
</template>

<script setup>
import { useGlobalIdleMonitoring } from '@/Composables/useIdleMonitoring'

const {
  isIdleMonitoringActive,
  currentUser,
  isWarningModalVisible,
  currentWarningCount,
  currentCountdown
} = useGlobalIdleMonitoring()
</script>
```

### Admin Dashboard

```javascript
import { useGlobalIdleMonitoring } from '@/Composables/useIdleMonitoring'

export function useAdminDashboard() {
  const { on: onIdleEvent, getStatus } = useGlobalIdleMonitoring()

  // Track all idle events for admin monitoring
  onIdleEvent('idle:timeout', (data) => {
    // Log warning for admin tracking
    console.log('User warning:', data)
  })

  onIdleEvent('logout:forced', (data) => {
    // Alert admin of forced logout
    console.log('User force logged out:', data)
  })

  const getMonitoringStatus = () => {
    return getStatus()
  }

  return { getMonitoringStatus }
}
```

### Settings Management

```javascript
import { useGlobalIdleMonitoring } from '@/Composables/useIdleMonitoring'

export function useIdleSettings() {
  const { currentSettings, updateSettings, on: onIdleEvent } = useGlobalIdleMonitoring()

  // Listen for settings updates
  onIdleEvent('settings:updated', (data) => {
    console.log('Settings updated:', data.settings)
  })

  const updateTimeout = (timeout) => {
    updateSettings({ idle_timeout: timeout })
  }

  const toggleMonitoring = (enabled) => {
    updateSettings({ idle_monitoring_enabled: enabled })
  }

  return {
    currentSettings,
    updateTimeout,
    toggleMonitoring
  }
}
```

## Best Practices

1. **Use the Global Instance**: Always use `useGlobalIdleMonitoring()` to ensure singleton behavior
2. **Clean Up Event Listeners**: The composable automatically cleans up on unmount
3. **Handle Errors Gracefully**: Listen to error events and provide fallbacks
4. **Test Event Handling**: Mock the event system in your tests
5. **Monitor Performance**: Use the timer events to track performance metrics

## Migration from Old Implementation

If you're migrating from the old IdleMonitor component:

1. Replace direct state access with computed properties
2. Use the event system instead of direct method calls
3. Remove manual timer management - it's handled automatically
4. Update your components to use the new API

## Troubleshooting

### Common Issues

1. **Multiple Instances**: Always use `useGlobalIdleMonitoring()` to avoid multiple instances
2. **Memory Leaks**: The composable automatically cleans up, but ensure you're not holding references
3. **Event Not Firing**: Check that you're using the correct event names and the monitoring is active

### Debug Mode

Enable debug logging by listening to all events:

```javascript
const { on: onIdleEvent } = useGlobalIdleMonitoring()

// Log all events for debugging
onIdleEvent('*', (eventName, data) => {
  console.log(`Idle Event: ${eventName}`, data)
})
```

## License

This composable is part of the Duaya Task project and follows the same licensing terms.
