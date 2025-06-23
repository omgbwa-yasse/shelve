import React, { useState } from 'react';
import { useMutation } from 'react-query';
import { toast } from 'react-toastify';
import shelveApi from '../../services/shelveApi';
import { validateEmail } from '../../utils/validators';
import Loading from '../common/Loading';
import { Button, Input, TextArea, Select, Radio } from '../forms/FormComponents';

const FeedbackPage = () => {
  const [formData, setFormData] = useState({
    type: 'suggestion',
    subject: '',
    email: '',
    name: '',
    message: '',
    page_url: window.location.href,
    user_agent: navigator.userAgent,
    rating: '',
    category: '',
    priority: 'normal'
  });

  const [errors, setErrors] = useState({});
  const [isSubmitted, setIsSubmitted] = useState(false);

  // Submit feedback mutation
  const submitFeedbackMutation = useMutation(
    (data) => shelveApi.submitFeedback(data),
    {
      onSuccess: () => {
        toast.success('Votre commentaire a été envoyé avec succès !');
        setIsSubmitted(true);
        setFormData({
          type: 'suggestion',
          subject: '',
          email: '',
          name: '',
          message: '',
          page_url: window.location.href,
          user_agent: navigator.userAgent,
          rating: '',
          category: '',
          priority: 'normal'
        });
        setErrors({});
      },
      onError: (error) => {
        console.error('Error submitting feedback:', error);
        if (error.response?.data?.errors) {
          setErrors(error.response.data.errors);
        } else {
          toast.error('Erreur lors de l\'envoi du commentaire. Veuillez réessayer.');
        }
      }
    }
  );

  // Form validation
  const validateForm = () => {
    const newErrors = {};

    if (!formData.type) {
      newErrors.type = 'Le type de commentaire est requis';
    }

    if (!formData.subject || formData.subject.trim().length < 3) {
      newErrors.subject = 'Le sujet doit contenir au moins 3 caractères';
    }

    if (!formData.email || !validateEmail(formData.email)) {
      newErrors.email = 'Une adresse email valide est requise';
    }

    if (!formData.name || formData.name.trim().length < 2) {
      newErrors.name = 'Le nom doit contenir au moins 2 caractères';
    }

    if (!formData.message || formData.message.trim().length < 10) {
      newErrors.message = 'Le message doit contenir au moins 10 caractères';
    }

    if (formData.type === 'rating' && !formData.rating) {
      newErrors.rating = 'Une note est requise pour ce type de commentaire';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Handle form submission
  const handleSubmit = (e) => {
    e.preventDefault();

    if (!validateForm()) {
      toast.error('Veuillez corriger les erreurs dans le formulaire');
      return;
    }

    submitFeedbackMutation.mutate(formData);
  };

  // Handle input changes
  const handleChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));

    // Clear specific error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  // Reset form
  const resetForm = () => {
    setIsSubmitted(false);
    setFormData({
      type: 'suggestion',
      subject: '',
      email: '',
      name: '',
      message: '',
      page_url: window.location.href,
      user_agent: navigator.userAgent,
      rating: '',
      category: '',
      priority: 'normal'
    });
    setErrors({});
  };

  if (isSubmitted) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-2xl mx-auto">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div className="text-green-500 mb-4">
              <svg className="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 className="text-2xl font-bold text-gray-900 mb-4">
              Merci pour votre commentaire !
            </h2>
            <p className="text-gray-600 mb-6">
              Votre message a été envoyé avec succès. Nous examinerons votre commentaire et vous répondrons si nécessaire.
            </p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Button
                onClick={resetForm}
                variant="outline"
                className="flex items-center justify-center"
              >
                <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                Envoyer un autre commentaire
              </Button>
              <Button
                onClick={() => window.history.back()}
                variant="primary"
                className="flex items-center justify-center"
              >
                <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
              </Button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-2xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">
            Commentaires et suggestions
          </h1>
          <p className="text-lg text-gray-600">
            Votre avis nous intéresse ! Partagez vos commentaires, suggestions ou signalez des problèmes.
          </p>
        </div>

        {/* Feedback form */}
        <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-sm border border-gray-200 p-8" noValidate>
          {/* Type of feedback */}
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-3">
              Type de commentaire *
            </label>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <Radio
                name="feedback-type"
                value="suggestion"
                checked={formData.type === 'suggestion'}
                onChange={(checked) => checked && handleChange('type', 'suggestion')}
                label="Suggestion d'amélioration"
              />
              <Radio
                name="feedback-type"
                value="bug"
                checked={formData.type === 'bug'}
                onChange={(checked) => checked && handleChange('type', 'bug')}
                label="Signalement de problème"
              />
              <Radio
                name="feedback-type"
                value="rating"
                checked={formData.type === 'rating'}
                onChange={(checked) => checked && handleChange('type', 'rating')}
                label="Évaluation générale"
              />
              <Radio
                name="feedback-type"
                value="question"
                checked={formData.type === 'question'}
                onChange={(checked) => checked && handleChange('type', 'question')}
                label="Question"
              />
            </div>
            {errors.type && (
              <p className="mt-2 text-sm text-red-600" role="alert">{errors.type}</p>
            )}
          </div>

          {/* Rating (if rating type selected) */}
          {formData.type === 'rating' && (
            <div className="mb-6">
              <label className="block text-sm font-medium text-gray-700 mb-3">
                Note générale *
              </label>
              <div className="flex space-x-2">
                {[1, 2, 3, 4, 5].map((star) => (
                  <button
                    key={star}
                    type="button"
                    onClick={() => handleChange('rating', star.toString())}
                    className={`p-1 rounded transition-colors ${
                      parseInt(formData.rating) >= star
                        ? 'text-yellow-500'
                        : 'text-gray-300 hover:text-yellow-400'
                    }`}
                    aria-label={`Noter ${star} étoile${star > 1 ? 's' : ''}`}
                  >
                    <svg className="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                  </button>
                ))}
              </div>
              {errors.rating && (
                <p className="mt-2 text-sm text-red-600" role="alert">{errors.rating}</p>
              )}
            </div>
          )}

          {/* Category */}
          <div className="mb-6">
            <Select
              label="Catégorie"
              value={formData.category}
              onChange={(value) => handleChange('category', value)}
              options={[
                { value: '', label: 'Sélectionner une catégorie' },
                { value: 'interface', label: 'Interface utilisateur' },
                { value: 'performance', label: 'Performance' },
                { value: 'content', label: 'Contenu' },
                { value: 'accessibility', label: 'Accessibilité' },
                { value: 'mobile', label: 'Version mobile' },
                { value: 'search', label: 'Recherche' },
                { value: 'other', label: 'Autre' }
              ]}
              placeholder="Choisissez une catégorie..."
            />
          </div>

          {/* Personal information */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <Input
              label="Nom complet"
              type="text"
              value={formData.name}
              onChange={(value) => handleChange('name', value)}
              error={errors.name}
              required
              placeholder="Votre nom"
              autoComplete="name"
            />

            <Input
              label="Adresse email"
              type="email"
              value={formData.email}
              onChange={(value) => handleChange('email', value)}
              error={errors.email}
              required
              placeholder="votre@email.com"
              autoComplete="email"
            />
          </div>

          {/* Subject */}
          <div className="mb-6">
            <Input
              label="Sujet"
              type="text"
              value={formData.subject}
              onChange={(value) => handleChange('subject', value)}
              error={errors.subject}
              required
              placeholder="Résumé de votre commentaire"
              maxLength={100}
            />
          </div>

          {/* Message */}
          <div className="mb-6">
            <TextArea
              label="Message détaillé"
              value={formData.message}
              onChange={(value) => handleChange('message', value)}
              error={errors.message}
              required
              placeholder="Décrivez en détail votre commentaire, suggestion ou problème..."
              rows={6}
              maxLength={2000}
            />
            <p className="mt-1 text-xs text-gray-500">
              {formData.message.length}/2000 caractères
            </p>
          </div>

          {/* Priority (for bugs and questions) */}
          {(formData.type === 'bug' || formData.type === 'question') && (
            <div className="mb-6">
              <Select
                label="Priorité"
                value={formData.priority}
                onChange={(value) => handleChange('priority', value)}
                options={[
                  { value: 'low', label: 'Faible' },
                  { value: 'normal', label: 'Normale' },
                  { value: 'high', label: 'Élevée' },
                  { value: 'urgent', label: 'Urgente' }
                ]}
              />
            </div>
          )}

          {/* Technical information notice */}
          <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div className="flex">
              <svg className="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
              </svg>
              <div className="text-sm text-blue-700">
                <p className="font-medium mb-1">Informations techniques automatiques</p>
                <p>
                  Pour nous aider à mieux comprendre votre problème, certaines informations techniques
                  (URL de la page, navigateur utilisé) seront automatiquement incluses avec votre message.
                </p>
              </div>
            </div>
          </div>

          {/* Submit button */}
          <div className="flex flex-col sm:flex-row gap-3">
            <Button
              type="submit"
              variant="primary"
              disabled={submitFeedbackMutation.isLoading}
              className="flex-1 flex items-center justify-center"
            >
              {submitFeedbackMutation.isLoading ? (
                <>
                  <Loading size="sm" className="mr-2" />
                  Envoi en cours...
                </>
              ) : (
                <>
                  <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                  </svg>
                  Envoyer le commentaire
                </>
              )}
            </Button>

            <Button
              type="button"
              onClick={() => window.history.back()}
              variant="outline"
              disabled={submitFeedbackMutation.isLoading}
              className="flex items-center justify-center"
            >
              <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Annuler
            </Button>
          </div>
        </form>

        {/* Help information */}
        <div className="mt-8 bg-gray-50 rounded-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-3">
            Comment pouvons-nous vous aider ?
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
              <h4 className="font-medium text-gray-900 mb-2">🐛 Signaler un problème</h4>
              <p>Décrivez les étapes pour reproduire le problème et votre environnement (navigateur, système).</p>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 mb-2">💡 Suggérer une amélioration</h4>
              <p>Partagez vos idées pour améliorer l'interface, les fonctionnalités ou l'expérience utilisateur.</p>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 mb-2">⭐ Évaluer le site</h4>
              <p>Donnez votre avis général sur le site et aidez-nous à identifier les points forts et faibles.</p>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 mb-2">❓ Poser une question</h4>
              <p>Besoin d'aide pour utiliser une fonctionnalité ou comprendre comment naviguer sur le site ?</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default FeedbackPage;
