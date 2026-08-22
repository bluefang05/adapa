# Flutter Rules
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.**  { *; }
-keep class io.flutter.util.**  { *; }
-keep class io.flutter.view.**  { *; }
-keep class io.flutter.**  { *; }
-keep class io.flutter.plugins.**  { *; }
-dontwarn io.flutter.**
-dontwarn com.google.android.play.core.**

# AndroidX Room (Required for WorkDatabase & SQLite reflection)
-keep class * extends androidx.room.RoomDatabase {
    public <init>();
    public <init>(...);
    *;
}
-keep class androidx.room.** { *; }
-dontwarn androidx.room.**
-keep class androidx.work.impl.WorkDatabase_Impl {
    public <init>();
    *;
}

# AndroidX WorkManager & Startup
-keep class androidx.work.** { *; }
-keep class androidx.work.impl.** { *; }
-keep class * extends androidx.work.Worker {
    public <init>(android.content.Context, androidx.work.WorkerParameters);
}
-keep class * extends androidx.work.ListenableWorker {
    public <init>(android.content.Context, androidx.work.WorkerParameters);
}
-keep class * extends androidx.work.InputMerger {
    public <init>();
}
-keep class androidx.startup.** { *; }
-keep class * extends androidx.startup.Initializer {
    public <init>();
}
-dontwarn androidx.work.**
-dontwarn androidx.startup.**

# Google Mobile Ads (AdMob)
-keep class com.google.android.gms.ads.** { *; }
-keep class com.google.ads.** { *; }
-keep class com.google.android.gms.internal.ads.** { *; }
-dontwarn com.google.android.gms.**

# General library suppressions
-dontwarn com.google.errorprone.annotations.**
-dontwarn org.checkerframework.**
-dontwarn javax.annotation.**

# Flutter Plugins
-keep class io.flutter.plugins.googlemobileads.** { *; }
-keep class com.google.flutter.** { *; }
