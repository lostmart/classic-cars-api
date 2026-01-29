const { Octokit } = require("@octokit/core")
const { execSync } = require("child_process")
const semver = require("semver")
const fs = require("fs")
const path = require("path")

class VersionManager {
	constructor(config) {
		this.config = config.versioning
		this.octokit = new Octokit({ auth: config.github.token })
		this.repoOwner = config.github.repoOwner
		this.repoName = config.github.repoName
	}

	async manageVersion(action = "bump") {
		switch (action) {
			case "bump":
				await this.bumpVersion()
				break
			case "release":
				await this.createRelease()
				break
			case "history":
				await this.showVersionHistory()
				break
			case "changelog":
				await this.generateChangelog()
				break
			default:
				console.log("Available actions: bump, release, history, changelog")
		}
	}

	async bumpVersion() {
		const currentVersion = this.getCurrentVersion()
		const newVersion = await this.determineNewVersion(currentVersion)

		if (newVersion === currentVersion) {
			console.log("No version bump needed")
			return
		}

		console.log(`Bumping version: ${currentVersion} → ${newVersion}`)

		await this.updatePackageVersion(newVersion)

		if (this.config.autoTag) {
			await this.createTag(newVersion)
		}

		if (this.config.releaseNotes) {
			await this.generateReleaseNotes(newVersion)
		}

		console.log(`✅ Version bumped to ${newVersion}`)
		return newVersion
	}

	getCurrentVersion() {
		try {
			const packageJson = JSON.parse(fs.readFileSync("package.json", "utf8"))
			return packageJson.version || "1.0.0"
		} catch (error) {
			console.warn("Could not read package.json, using default version")
			return "1.0.0"
		}
	}

	async determineNewVersion(currentVersion) {
		switch (this.config.strategy) {
			case "semantic":
				return await this.semanticBump(currentVersion)
			case "calendar":
				return this.calendarBump(currentVersion)
			case "custom":
				return this.customBump(currentVersion)
			default:
				return await this.semanticBump(currentVersion)
		}
	}

	async semanticBump(currentVersion) {
		const commitHistory = this.getCommitHistory()
		const commitMessages = commitHistory
			.split("\n")
			.filter((line) => line.trim())

		let bump = "patch"

		for (const message of commitMessages) {
			const msgLower = message.toLowerCase()

			for (const [type, keywords] of Object.entries(this.config.bumpRules)) {
				if (keywords.some((keyword) => msgLower.includes(keyword))) {
					if (type === "major") {
						bump = "major"
						break
					} else if (type === "minor" && bump !== "major") {
						bump = "minor"
					}
				}
			}

			if (bump === "major") break
		}

		return semver.inc(currentVersion, bump)
	}

	calendarBump(currentVersion) {
		const today = new Date()
		const year = today.getFullYear()
		const month = String(today.getMonth() + 1).padStart(2, "0")
		const day = String(today.getDate()).padStart(2, "0")

		const calendarVersion = `${year}.${month}.${day}`

		if (semver.gt(calendarVersion, currentVersion)) {
			return calendarVersion
		}

		const parts = currentVersion.split(".")
		const patch = parseInt(parts[2] || "0") + 1
		return `${year}.${month}.${patch}`
	}

	customBump(currentVersion) {
		const parts = currentVersion.split(".")
		const minor = parseInt(parts[1] || "0") + 1
		return `${parts[0]}.${minor}.0`
	}

	getCommitHistory() {
		try {
			const tag = this.getLatestTag()
			const range = tag ? `${tag}..HEAD` : "HEAD"
			return execSync(`git log ${range} --pretty=format:"%s"`, {
				encoding: "utf8",
			})
		} catch (error) {
			return ""
		}
	}

	getLatestTag() {
		try {
			return execSync("git describe --tags --abbrev=0", {
				encoding: "utf8",
			}).trim()
		} catch (error) {
			return null
		}
	}

	async updatePackageVersion(newVersion) {
		const packageJsonPath = "package.json"
		const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, "utf8"))
		packageJson.version = newVersion
		fs.writeFileSync(
			packageJsonPath,
			JSON.stringify(packageJson, null, 2) + "\n",
		)

		try {
			execSync(
				`git add package.json && git commit -m "chore: bump version to ${newVersion}"`,
				{ encoding: "utf8" },
			)
		} catch (error) {
			console.warn("Could not commit version bump:", error.message)
		}
	}

	async createTag(version) {
		try {
			const tagMessage = `Release ${version}`
			execSync(`git tag -a ${version} -m "${tagMessage}"`, { encoding: "utf8" })
			execSync(`git push origin ${version}`, { encoding: "utf8" })
			console.log(`✅ Tag ${version} created and pushed`)
		} catch (error) {
			console.error("Failed to create tag:", error.message)
		}
	}

	async createRelease() {
		const version = this.getCurrentVersion()
		const releaseNotes = await this.generateReleaseNotesContent(version)

		try {
			const response = await this.octokit.request(
				"POST /repos/{owner}/{repo}/releases",
				{
					owner: this.repoOwner,
					repo: this.repoName,
					tag_name: version,
					name: `Release ${version}`,
					body: releaseNotes,
					draft: false,
					prerelease: version.includes("-"),
				},
			)

			console.log(`✅ Release ${version} created`)
			return response.data
		} catch (error) {
			console.error("Failed to create release:", error.message)
		}
	}

	async generateReleaseNotes(version) {
		const content = await this.generateReleaseNotesContent(version)

		if (this.config.releaseNotes) {
			fs.writeFileSync(
				"CHANGELOG.md",
				content +
					(fs.existsSync("CHANGELOG.md")
						? "\n\n" + fs.readFileSync("CHANGELOG.md", "utf8")
						: ""),
			)
		}

		return content
	}

	async generateReleaseNotesContent(version) {
		const tag = this.getLatestTag()
		const range = tag ? `${tag}...${version}` : `${version}`

		try {
			const commits = execSync(`git log ${range} --pretty=format:"- %s (%h)"`, {
				encoding: "utf8",
			})
			const commitLines = commits.split("\n").filter((line) => line.trim())

			const features = commitLines.filter((line) =>
				line.toLowerCase().includes("feat"),
			)
			const fixes = commitLines.filter((line) =>
				line.toLowerCase().includes("fix"),
			)
			const other = commitLines.filter(
				(line) =>
					!line.toLowerCase().includes("feat") &&
					!line.toLowerCase().includes("fix"),
			)

			let content = `# ${version}\n\n`

			if (features.length > 0) {
				content += "## Features\n" + features.join("\n") + "\n\n"
			}

			if (fixes.length > 0) {
				content += "## Bug Fixes\n" + fixes.join("\n") + "\n\n"
			}

			if (other.length > 0) {
				content += "## Other Changes\n" + other.join("\n") + "\n\n"
			}

			return content
		} catch (error) {
			return `# ${version}\n\nRelease notes could not be generated automatically.`
		}
	}

	async showVersionHistory() {
		try {
			const tags = execSync("git tag --sort=-version:refname", {
				encoding: "utf8",
			})
			const tagList = tags.split("\n").filter((tag) => tag.trim())

			console.log("Version History:")
			tagList.forEach((tag, index) => {
				const date = execSync(`git log -1 --format=%ai ${tag}`, {
					encoding: "utf8",
				}).trim()
				console.log(`${index + 1}. ${tag} (${date})`)
			})
		} catch (error) {
			console.error("Failed to get version history:", error.message)
		}
	}

	async generateChangelog() {
		const content = await this.generateReleaseNotesContent(
			this.getCurrentVersion(),
		)
		console.log("Generated changelog:")
		console.log(content)

		if (this.config.releaseNotes) {
			fs.writeFileSync(
				"CHANGELOG.md",
				content +
					(fs.existsSync("CHANGELOG.md")
						? "\n\n" + fs.readFileSync("CHANGELOG.md", "utf8")
						: ""),
			)
			console.log("✅ Changelog updated in CHANGELOG.md")
		}
	}

	async isReleaseNeeded() {
		const currentVersion = this.getCurrentVersion()
		const newVersion = await this.determineNewVersion(currentVersion)
		return newVersion !== currentVersion
	}
}

module.exports = VersionManager
